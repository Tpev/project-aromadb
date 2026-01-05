<?php

namespace App\Http\Controllers;

use App\Models\DigitalTraining;
use App\Models\TrainingModule;
use App\Models\TrainingBlock;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DigitalTrainingController extends Controller
{
    /* =========================================================
     * INDEX : list all trainings for therapist
     * ======================================================= */
    public function index()
    {
        $user = Auth::user();

        if ($user->license_status === 'inactive') {
            return redirect('/license-tiers/pricing');
        }

        $trainings = DigitalTraining::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('digital-trainings.index', compact('trainings'));
    }

    /* =========================================================
     * CREATE / STORE
     * ======================================================= */

    public function create()
    {
        $user = Auth::user();

        $products = Product::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $training = new DigitalTraining();

        return view('digital-trainings.create', compact('training', 'products'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'cover_image'                => 'nullable|image|max:8048',
            'tags'                       => 'nullable|string',

            // Pricing
            'is_free'                    => 'nullable|boolean',
            'price_eur'                  => 'nullable|string', // parsed manually to allow "12,50"
            'tax_rate'                   => 'nullable|numeric|min:0|max:100',

            'access_type'                => 'required|in:public,private,subscription',
            'status'                     => 'required|in:draft,published,archived',
            'estimated_duration_minutes' => 'nullable|integer|min:1',

            'product_id'                 => [
                'nullable',
                Rule::exists('products', 'id')->where(fn($q) => $q->where('user_id', $user->id)),
            ],
        ]);

        // -------- Pricing normalize
        $isFree = (bool) ($request->boolean('is_free'));
        $priceCents = null;

        if (!$isFree) {
            // require price for paid trainings
            if ($request->filled('price_eur') === false) {
                return back()->withErrors(['price_eur' => 'Veuillez indiquer un prix (ou cocher "Formation gratuite").'])->withInput();
            }

            $raw = (string) $request->input('price_eur');
            $raw = str_replace([' ', '€'], '', $raw);
            $raw = str_replace(',', '.', $raw);

            if (!is_numeric($raw)) {
                return back()->withErrors(['price_eur' => 'Veuillez indiquer un prix valide.'])->withInput();
            }

            $eur = (float) $raw;
            if ($eur < 0) {
                return back()->withErrors(['price_eur' => 'Le prix ne peut pas être négatif.'])->withInput();
            }

            $priceCents = (int) round($eur * 100);
        }

        $taxRate = $request->filled('tax_rate') ? (float) $request->input('tax_rate') : 0.0;

        // -------- Slug unique
        $slugBase = Str::slug($data['title']);
        $slug     = $slugBase;
        $i        = 1;
        while (DigitalTraining::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $i++;
        }

        // -------- Cover image
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('digital-trainings/covers', 'public');
        }

        // -------- Tags: "stress, sommeil" -> ['stress', 'sommeil']
        $tags = null;
        if (!empty($data['tags'])) {
            $tags = collect(explode(',', $data['tags']))
                ->map(fn ($t) => trim($t))
                ->filter()
                ->values()
                ->toArray();
        }

        $training = DigitalTraining::create([
            'user_id'                    => $user->id,
            'title'                      => $data['title'],
            'slug'                       => $slug,
            'description'                => $data['description'] ?? null,
            'cover_image_path'           => $coverPath,
            'tags'                       => $tags,

            // Pricing
            'is_free'                    => $isFree,
            'price_cents'                => $priceCents,
            'tax_rate'                   => $taxRate,

            'access_type'                => $data['access_type'],
            'status'                     => $data['status'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'product_id'                 => $data['product_id'] ?? null,
        ]);

        return redirect()
            ->route('digital-trainings.builder', $training)
            ->with('success', 'Formation digitale créée. Vous pouvez maintenant ajouter des modules et du contenu.');
    }

    /* =========================================================
     * EDIT / UPDATE / DELETE
     * ======================================================= */

    public function edit(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $user = Auth::user();

        $products = Product::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        $training = $digitalTraining;

        return view('digital-trainings.edit', compact('training', 'products'));
    }

    public function update(Request $request, DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $user = Auth::user();

        $data = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'cover_image'                => 'nullable|image|max:8048',
            'tags'                       => 'nullable|string',

            // Pricing
            'is_free'                    => 'nullable|boolean',
            'price_eur'                  => 'nullable|string',
            'tax_rate'                   => 'nullable|numeric|min:0|max:100',

            'access_type'                => 'required|in:public,private,subscription',
            'status'                     => 'required|in:draft,published,archived',
            'estimated_duration_minutes' => 'nullable|integer|min:1',

            'product_id'                 => [
                'nullable',
                Rule::exists('products', 'id')->where(fn($q) => $q->where('user_id', $user->id)),
            ],
        ]);

        // Cover replacement
        if ($request->hasFile('cover_image')) {
            if ($digitalTraining->cover_image_path) {
                Storage::disk('public')->delete($digitalTraining->cover_image_path);
            }

            $digitalTraining->cover_image_path = $request->file('cover_image')
                ->store('digital-trainings/covers', 'public');
        }

        // Tags
        $tags = null;
        if (!empty($data['tags'])) {
            $tags = collect(explode(',', $data['tags']))
                ->map(fn ($t) => trim($t))
                ->filter()
                ->values()
                ->toArray();
        }

        // Pricing normalize
        $isFree = (bool) ($request->boolean('is_free'));
        $priceCents = null;

        if (!$isFree) {
            if ($request->filled('price_eur') === false) {
                return back()->withErrors(['price_eur' => 'Veuillez indiquer un prix (ou cocher "Formation gratuite").'])->withInput();
            }

            $raw = (string) $request->input('price_eur');
            $raw = str_replace([' ', '€'], '', $raw);
            $raw = str_replace(',', '.', $raw);

            if (!is_numeric($raw)) {
                return back()->withErrors(['price_eur' => 'Veuillez indiquer un prix valide.'])->withInput();
            }

            $eur = (float) $raw;
            if ($eur < 0) {
                return back()->withErrors(['price_eur' => 'Le prix ne peut pas être négatif.'])->withInput();
            }

            $priceCents = (int) round($eur * 100);
        }

        $taxRate = $request->filled('tax_rate') ? (float) $request->input('tax_rate') : 0.0;

        $digitalTraining->update([
            'title'                      => $data['title'],
            'description'                => $data['description'] ?? null,
            'tags'                       => $tags,

            // Pricing
            'is_free'                    => $isFree,
            'price_cents'                => $priceCents,
            'tax_rate'                   => $taxRate,

            'access_type'                => $data['access_type'],
            'status'                     => $data['status'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
            'product_id'                 => $data['product_id'] ?? null,
        ]);

        return redirect()
            ->route('digital-trainings.builder', $digitalTraining)
            ->with('success', 'Formation mise à jour.');
    }

    public function destroy(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        if ($digitalTraining->cover_image_path) {
            Storage::disk('public')->delete($digitalTraining->cover_image_path);
        }

        $digitalTraining->delete();

        return redirect()
            ->route('digital-trainings.index')
            ->with('success', 'Formation supprimée.');
    }

    /* =========================================================
     * BUILDER (modules + blocks)
     * ======================================================= */

    public function builder(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $training = $digitalTraining->load('modules.blocks');

        return view('digital-trainings.builder', compact('training'));
    }

    /* =========================================================
     * MODULES
     * ======================================================= */

    public function storeModule(Request $request, DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $maxOrder = $digitalTraining->modules()->max('display_order') ?? 0;

        $digitalTraining->modules()->create([
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'display_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Module ajouté.');
    }

    public function updateModule(Request $request, DigitalTraining $digitalTraining, TrainingModule $module)
    {
        $this->authorizeOwner($digitalTraining);
        $this->authorizeModule($digitalTraining, $module);

        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $module->update($data);

        return back()->with('success', 'Module mis à jour.');
    }

    public function destroyModule(DigitalTraining $digitalTraining, TrainingModule $module)
    {
        $this->authorizeOwner($digitalTraining);
        $this->authorizeModule($digitalTraining, $module);

        $module->delete();

        return back()->with('success', 'Module supprimé.');
    }

    /* =========================================================
     * BLOCKS (content)
     * ======================================================= */

    public function storeBlock(Request $request, DigitalTraining $digitalTraining, TrainingModule $module)
    {
        $this->authorizeOwner($digitalTraining);
        $this->authorizeModule($digitalTraining, $module);

        $data = $request->validate([
            'type'    => 'required|in:text,video_url,pdf',
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'file'    => 'nullable|file|max:512000', // 500MB
        ]);

        $filePath = null;

        if ($request->hasFile('file')) {
            if ($data['type'] === 'pdf') {
                $request->validate([
                    'file' => 'file|mimes:pdf|max:20480',
                ]);
                $filePath = $request->file('file')->store('digital-trainings/blocks', 'public');
            }

            if ($data['type'] === 'video_url') {
                $request->validate([
                    'file' => 'file|mimes:mp4,mov,webm,ogg|max:512000',
                ]);
                $filePath = $request->file('file')->store('digital-trainings/videos', 'public');
            }
        }

        $maxOrder = $module->blocks()->max('display_order') ?? 0;

        $module->blocks()->create([
            'type'          => $data['type'],
            'title'         => $data['title'] ?? null,
            'content'       => $data['type'] === 'pdf' ? null : ($data['content'] ?? null),
            'file_path'     => $filePath,
            'display_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Contenu ajouté au module.');
    }

    public function updateBlock(Request $request, DigitalTraining $digitalTraining, TrainingModule $module, TrainingBlock $block)
    {
        $this->authorizeOwner($digitalTraining);
        $this->authorizeModule($digitalTraining, $module);
        $this->authorizeBlock($module, $block);

        $data = $request->validate([
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'file'    => 'nullable|file|max:512000',
        ]);

        if ($request->hasFile('file')) {
            if ($block->type === 'pdf') {
                $request->validate([
                    'file' => 'file|mimes:pdf|max:20480',
                ]);

                if ($block->file_path) {
                    Storage::disk('public')->delete($block->file_path);
                }
                $block->file_path = $request->file('file')->store('digital-trainings/blocks', 'public');
            }

            if ($block->type === 'video_url') {
                $request->validate([
                    'file' => 'file|mimes:mp4,mov,webm,ogg|max:512000',
                ]);

                if ($block->file_path) {
                    Storage::disk('public')->delete($block->file_path);
                }
                $block->file_path = $request->file('file')->store('digital-trainings/videos', 'public');
            }
        }

        $block->title = $data['title'] ?? $block->title;

        if ($block->type !== 'pdf') {
            $block->content = $data['content'] ?? $block->content;
        }

        $block->save();

        return back()->with('success', 'Contenu mis à jour.');
    }

    public function destroyBlock(DigitalTraining $digitalTraining, TrainingModule $module, TrainingBlock $block)
    {
        $this->authorizeOwner($digitalTraining);
        $this->authorizeModule($digitalTraining, $module);
        $this->authorizeBlock($module, $block);

        if ($block->file_path) {
            Storage::disk('public')->delete($block->file_path);
        }

        $block->delete();

        return back()->with('success', 'Contenu supprimé.');
    }

    /* =========================================================
     * PREVIEW
     * ======================================================= */

    public function preview(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $training = $digitalTraining->load('modules.blocks');

        return view('digital-trainings.preview', compact('training'));
    }

    /* =========================================================
     * PUBLIC SHOW PAGE
     * ======================================================= */

    public function publicShow(DigitalTraining $digitalTraining)
    {
        if ($digitalTraining->status !== 'published') {
            abort(404);
        }

        $training  = $digitalTraining->load('user', 'modules.blocks');
        $therapist = $training->user;

        $therapistPublicUrl = null;
        if ($therapist) {
            $slug = $therapist->slug ?? $therapist->pro_slug ?? null;
            if ($slug) {
                $therapistPublicUrl = url('/pro/' . $slug);
            }
        }

        return view('digital-trainings.public.show', compact(
            'training',
            'therapist',
            'therapistPublicUrl'
        ));
    }

    /* =========================================================
     * Small helpers
     * ======================================================= */

    protected function authorizeOwner(DigitalTraining $training): void
    {
        if ($training->user_id !== Auth::id()) {
            abort(403);
        }
    }

    protected function authorizeModule(DigitalTraining $training, TrainingModule $module): void
    {
        if ($module->digital_training_id !== $training->id) {
            abort(403);
        }
    }

    protected function authorizeBlock(TrainingModule $module, TrainingBlock $block): void
    {
        if ($block->training_module_id !== $module->id) {
            abort(403);
        }
    }
}
