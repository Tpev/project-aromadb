<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Product;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class MobileProductController extends Controller
{
    public function index()
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        $products = Product::query()
            ->withCount(['availabilities', 'invoiceItems'])
            ->where('user_id', Auth::id())
            ->orderBy('display_order')
            ->latest('id')
            ->get();

        return view('mobile.products.index', compact('products'));
    }

    public function create()
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        return view('mobile.products.form', [
            'title' => 'Nouvelle prestation',
            'product' => new Product([
                'tax_rate' => 0,
                'duration' => 60,
                'can_be_booked_online' => true,
                'collect_payment' => false,
                'requires_emargement' => false,
                'visible_in_portal' => true,
                'price_visible_in_portal' => true,
                'display_order' => 0,
            ]),
            'mode' => old('mode', 'dans_le_cabinet'),
            'questionnaires' => $this->ownedQuestionnaires(),
            'action' => route('mobile.products.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        $payload = $this->validatedProductPayload($request);
        $payload['user_id'] = Auth::id();

        $product = Product::create($payload);

        return redirect()
            ->route('mobile.products.show', $product)
            ->with('success', 'Prestation creee.');
    }

    public function show(Product $product)
    {
        $this->ensureOwnsProduct($product);

        $product->load(['availabilities.practiceLocation', 'bookingQuestionnaire'])
            ->loadCount(['availabilities', 'invoiceItems']);

        $appointmentsCount = Appointment::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->count();

        $upcomingAppointmentsCount = Appointment::query()
            ->where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->where('appointment_date', '>=', now()->toDateString())
            ->count();

        return view('mobile.products.show', [
            'product' => $product,
            'mode' => $this->productMode($product),
            'appointmentsCount' => $appointmentsCount,
            'upcomingAppointmentsCount' => $upcomingAppointmentsCount,
        ]);
    }

    public function edit(Product $product)
    {
        $this->ensureOwnsProduct($product);

        return view('mobile.products.form', [
            'title' => 'Modifier la prestation',
            'product' => $product,
            'mode' => old('mode', $this->productMode($product)),
            'questionnaires' => $this->ownedQuestionnaires(),
            'action' => route('mobile.products.update', $product),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $this->ensureOwnsProduct($product);

        $product->update($this->validatedProductPayload($request));

        return redirect()
            ->route('mobile.products.show', $product)
            ->with('success', 'Prestation mise a jour.');
    }

    public function destroy(Product $product)
    {
        $this->ensureOwnsProduct($product);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        if ($product->brochure) {
            Storage::disk('public')->delete($product->brochure);
        }

        $product->delete();

        return redirect()
            ->route('mobile.products.index')
            ->with('success', 'Prestation supprimee.');
    }

    private function validatedProductPayload(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'mode' => ['required', 'string', 'in:visio,adomicile,en_entreprise,dans_le_cabinet'],
            'max_per_day' => ['nullable', 'integer', 'min:1'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'can_be_booked_online' => ['nullable', 'boolean'],
            'collect_payment' => ['nullable', 'boolean'],
            'requires_emargement' => ['nullable', 'boolean'],
            'visible_in_portal' => ['nullable', 'boolean'],
            'price_visible_in_portal' => ['nullable', 'boolean'],
            'booking_questionnaire_enabled' => ['nullable', 'boolean'],
            'booking_questionnaire_id' => ['nullable', 'integer', 'exists:questionnaires,id'],
            'booking_questionnaire_frequency' => ['nullable', 'string', 'in:first_time_only,every_booking'],
        ]);

        $questionnaireEnabled = $request->boolean('booking_questionnaire_enabled');
        $questionnaireId = $questionnaireEnabled ? (int) ($validated['booking_questionnaire_id'] ?? 0) : null;

        if ($questionnaireEnabled) {
            if (method_exists(Auth::user(), 'canUseFeature') && ! Auth::user()->canUseFeature('questionnaires')) {
                throw ValidationException::withMessages([
                    'booking_questionnaire_enabled' => 'Votre formule ne permet pas encore les questionnaires automatiques.',
                ]);
            }

            $ownsQuestionnaire = Questionnaire::query()
                ->where('user_id', Auth::id())
                ->whereKey($questionnaireId)
                ->exists();

            if (! $ownsQuestionnaire) {
                throw ValidationException::withMessages([
                    'booking_questionnaire_id' => 'Selectionnez un questionnaire de votre compte.',
                ]);
            }
        }

        $modeFlags = [
            'visio' => false,
            'adomicile' => false,
            'en_entreprise' => false,
            'dans_le_cabinet' => false,
        ];
        $modeFlags[$validated['mode']] = true;

        return [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'],
            'tax_rate' => $validated['tax_rate'],
            'duration' => $validated['duration'] ?? null,
            'max_per_day' => $validated['max_per_day'] ?? null,
            'display_order' => $validated['display_order'] ?? 0,
            'can_be_booked_online' => $request->boolean('can_be_booked_online'),
            'collect_payment' => $request->boolean('collect_payment'),
            'requires_emargement' => $request->boolean('requires_emargement'),
            'visible_in_portal' => $request->boolean('visible_in_portal'),
            'price_visible_in_portal' => $request->boolean('price_visible_in_portal'),
            'booking_questionnaire_enabled' => $questionnaireEnabled,
            'booking_questionnaire_id' => $questionnaireEnabled ? $questionnaireId : null,
            'booking_questionnaire_frequency' => $questionnaireEnabled
                ? ($validated['booking_questionnaire_frequency'] ?? Product::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY)
                : null,
            ...$modeFlags,
        ];
    }

    private function ownedQuestionnaires(): Collection
    {
        return Questionnaire::query()
            ->where('user_id', Auth::id())
            ->orderBy('title')
            ->get();
    }

    private function ensureOwnsProduct(Product $product): void
    {
        abort_unless((int) $product->user_id === (int) Auth::id(), 403);
    }

    private function productMode(Product $product): string
    {
        if ($product->visio) {
            return 'visio';
        }

        if ($product->adomicile) {
            return 'adomicile';
        }

        if ($product->en_entreprise) {
            return 'en_entreprise';
        }

        return 'dans_le_cabinet';
    }

    private function inactiveLicenseRedirect()
    {
        return Auth::user()?->license_status === 'inactive'
            ? redirect('/license-tiers/pricing')
            : null;
    }
}
