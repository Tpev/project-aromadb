<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\DigitalTraining;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MobileDigitalTrainingController extends Controller
{
    public function index()
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        $trainings = DigitalTraining::query()
            ->withCount(['modules', 'enrollments'])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return view('mobile.digital-trainings.index', compact('trainings'));
    }

    public function create()
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        return view('mobile.digital-trainings.form', [
            'title' => 'Nouvelle formation',
            'training' => new DigitalTraining([
                'is_free' => false,
                'tax_rate' => 0,
                'access_type' => 'public',
                'status' => 'draft',
                'installments_enabled' => false,
                'allowed_installments' => [],
                'free_access_requires_identity' => false,
                'free_access_is_open' => false,
                'use_global_retractation_notice' => false,
            ]),
            'products' => $this->ownedProducts(),
            'action' => route('mobile.digital-trainings.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        $validated = $this->validatedPayload($request);

        $slug = $this->uniqueSlug((string) $validated['title']);
        $coverPath = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('digital-trainings/covers', 'public')
            : null;

        $training = DigitalTraining::create($this->trainingAttributes($validated) + [
            'user_id' => Auth::id(),
            'slug' => $slug,
            'cover_image_path' => $coverPath,
        ]);

        return redirect()
            ->route('mobile.digital-trainings.show', $training)
            ->with('success', 'Formation creee.');
    }

    public function show(DigitalTraining $digitalTraining)
    {
        $this->ensureOwnsTraining($digitalTraining);

        $digitalTraining->load([
            'product',
            'modules.blocks',
            'enrollments' => fn ($query) => $query->with('clientProfile')->latest('id')->limit(10),
        ])->loadCount(['modules', 'enrollments']);

        $blocksCount = $digitalTraining->modules
            ->sum(fn ($module) => $module->blocks->count());

        return view('mobile.digital-trainings.show', [
            'training' => $digitalTraining,
            'blocksCount' => $blocksCount,
        ]);
    }

    public function edit(DigitalTraining $digitalTraining)
    {
        $this->ensureOwnsTraining($digitalTraining);

        return view('mobile.digital-trainings.form', [
            'title' => 'Modifier la formation',
            'training' => $digitalTraining,
            'products' => $this->ownedProducts(),
            'action' => route('mobile.digital-trainings.update', $digitalTraining),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, DigitalTraining $digitalTraining)
    {
        $this->ensureOwnsTraining($digitalTraining);

        $validated = $this->validatedPayload($request, $digitalTraining);

        if ($request->hasFile('cover_image')) {
            if ($digitalTraining->cover_image_path) {
                Storage::disk('public')->delete($digitalTraining->cover_image_path);
            }

            $digitalTraining->cover_image_path = $request->file('cover_image')
                ->store('digital-trainings/covers', 'public');
        }

        $digitalTraining->fill($this->trainingAttributes($validated));
        $digitalTraining->save();

        return redirect()
            ->route('mobile.digital-trainings.show', $digitalTraining)
            ->with('success', 'Formation mise a jour.');
    }

    public function destroy(DigitalTraining $digitalTraining)
    {
        $this->ensureOwnsTraining($digitalTraining);

        if ($digitalTraining->cover_image_path) {
            Storage::disk('public')->delete($digitalTraining->cover_image_path);
        }

        $digitalTraining->delete();

        return redirect()
            ->route('mobile.digital-trainings.index')
            ->with('success', 'Formation supprimee.');
    }

    private function validatedPayload(Request $request, ?DigitalTraining $training = null): array
    {
        $userId = Auth::id();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'image', 'max:8048'],
            'tags' => ['nullable', 'string'],
            'is_free' => ['nullable', 'boolean'],
            'free_access_requires_identity' => ['nullable', 'boolean'],
            'free_access_is_open' => ['nullable', 'boolean'],
            'price_eur' => ['nullable', 'string'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'installments_enabled' => ['nullable', 'boolean'],
            'allowed_installments' => ['nullable', 'array'],
            'allowed_installments.*' => ['integer', 'min:2', 'max:12'],
            'access_type' => ['required', Rule::in(['public', 'private', 'subscription'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'estimated_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'use_global_retractation_notice' => ['nullable', 'boolean'],
            'product_id' => [
                'nullable',
                Rule::exists('products', 'id')->where(fn ($query) => $query->where('user_id', $userId)),
            ],
        ]);

        $isFree = $request->boolean('is_free');
        $priceCents = null;

        if (! $isFree) {
            if (! $request->filled('price_eur')) {
                throw ValidationException::withMessages([
                    'price_eur' => 'Veuillez indiquer un prix ou cocher formation gratuite.',
                ]);
            }

            $priceCents = $this->parsePriceCents((string) $request->input('price_eur'));
        }

        $allowedInstallments = collect($request->input('allowed_installments', []))
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value >= 2 && $value <= 12)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $installmentsEnabled = $request->boolean('installments_enabled');

        if ($installmentsEnabled && ($isFree || empty($allowedInstallments))) {
            throw ValidationException::withMessages([
                'allowed_installments' => 'Selectionnez au moins une echeance pour une formation payante.',
            ]);
        }

        $freeAccessIsOpen = $isFree && $request->boolean('free_access_is_open');

        $validated['is_free'] = $isFree;
        $validated['free_access_is_open'] = $freeAccessIsOpen;
        $validated['free_access_requires_identity'] = $isFree
            && ! $freeAccessIsOpen
            && $request->boolean('free_access_requires_identity');
        $validated['price_cents'] = $priceCents;
        $validated['tax_rate'] = $request->filled('tax_rate') ? (float) $request->input('tax_rate') : 0.0;
        $validated['installments_enabled'] = $installmentsEnabled;
        $validated['allowed_installments'] = $installmentsEnabled ? $allowedInstallments : null;
        $validated['tags_array'] = $this->parseTags((string) ($validated['tags'] ?? ''));
        $validated['product_id'] = $validated['product_id'] ?? null;
        $validated['estimated_duration_minutes'] = $validated['estimated_duration_minutes'] ?? null;
        $validated['use_global_retractation_notice'] = Schema::hasColumn('digital_trainings', 'use_global_retractation_notice')
            ? $request->boolean('use_global_retractation_notice')
            : (bool) ($training?->use_global_retractation_notice ?? false);

        return $validated;
    }

    private function trainingAttributes(array $validated): array
    {
        return [
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'tags' => $validated['tags_array'],
            'is_free' => $validated['is_free'],
            'free_access_requires_identity' => $validated['free_access_requires_identity'],
            'free_access_is_open' => $validated['free_access_is_open'],
            'price_cents' => $validated['price_cents'],
            'tax_rate' => $validated['tax_rate'],
            'installments_enabled' => $validated['installments_enabled'],
            'allowed_installments' => $validated['allowed_installments'],
            'access_type' => $validated['access_type'],
            'status' => $validated['status'],
            'estimated_duration_minutes' => $validated['estimated_duration_minutes'],
            'product_id' => $validated['product_id'],
            'use_global_retractation_notice' => $validated['use_global_retractation_notice'],
        ];
    }

    private function parsePriceCents(string $raw): int
    {
        $normalized = preg_replace('/[^\d,.\-]/', '', $raw) ?? '';
        $normalized = str_replace(',', '.', $normalized);

        if ($normalized === '' || ! is_numeric($normalized)) {
            throw ValidationException::withMessages([
                'price_eur' => 'Veuillez indiquer un prix valide.',
            ]);
        }

        $price = (float) $normalized;

        if ($price < 0) {
            throw ValidationException::withMessages([
                'price_eur' => 'Le prix ne peut pas etre negatif.',
            ]);
        }

        return (int) round($price * 100);
    }

    private function parseTags(string $raw): ?array
    {
        $tags = collect(explode(',', $raw))
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->values()
            ->all();

        return empty($tags) ? null : $tags;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'formation';
        $slug = $base;
        $index = 1;

        while (DigitalTraining::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $index++;
        }

        return $slug;
    }

    private function ownedProducts()
    {
        return Product::query()
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();
    }

    private function ensureOwnsTraining(DigitalTraining $training): void
    {
        abort_unless((int) $training->user_id === (int) Auth::id(), 403);
    }

    private function inactiveLicenseRedirect()
    {
        return Auth::user()?->license_status === 'inactive'
            ? redirect('/license-tiers/pricing')
            : null;
    }
}
