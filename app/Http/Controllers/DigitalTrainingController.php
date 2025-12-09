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
            'access_type'                => 'required|in:public,private,subscription',
            'status'                     => 'required|in:draft,published,archived',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'product_id'                 => 'nullable|exists:products,id',
        ]);

        // Slug unique
        $slugBase = Str::slug($data['title']);
        $slug     = $slugBase;
        $i        = 1;
        while (DigitalTraining::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $i++;
        }

        // Cover image
        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('digital-trainings/covers', 'public');
        }

        // Tags: "stress, sommeil" -> ['stress', 'sommeil']
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

        $data = $request->validate([
            'title'                      => 'required|string|max:255',
            'description'                => 'nullable|string',
            'cover_image'                => 'nullable|image|max:8048',
            'tags'                       => 'nullable|string',
            'access_type'                => 'required|in:public,private,subscription',
            'status'                     => 'required|in:draft,published,archived',
            'estimated_duration_minutes' => 'nullable|integer|min:1',
            'product_id'                 => 'nullable|exists:products,id',
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

        $digitalTraining->update([
            'title'                      => $data['title'],
            'description'                => $data['description'] ?? null,
            'tags'                       => $tags,
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
            'file'    => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $filePath = null;
        if ($data['type'] === 'pdf' && $request->hasFile('file')) {
            $filePath = $request->file('file')
                ->store('digital-trainings/blocks', 'public');
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
            'file'    => 'nullable|file|mimes:pdf|max:20480',
        ]);

        // For PDFs, allow replacing the file
        if ($block->type === 'pdf' && $request->hasFile('file')) {
            if ($block->file_path) {
                Storage::disk('public')->delete($block->file_path);
            }

            $block->file_path = $request->file('file')
                ->store('digital-trainings/blocks', 'public');
        }

        $block->title = $data['title'] ?? $block->title;

        if ($block->type !== 'pdf') {
            // For text / video_url, update content
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
     * PREVIEW (internal player-like view)
     * ======================================================= */

    public function preview(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $training = $digitalTraining->load('modules.blocks');

        return view('digital-trainings.preview', compact('training'));
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
	    /* =========================================================
     * PUBLIC SHOW PAGE (landing)
     * ======================================================= */

    /**
     * Public landing page for a digital training.
     * Example URL: /formations/mon-super-programme
     */
    public function publicShow(DigitalTraining $digitalTraining)
    {
        // Only show published trainings
        if ($digitalTraining->status !== 'published') {
            abort(404);
        }

        // Load therapist (owner)
        $training  = $digitalTraining->load('user');
        $therapist = $training->user;

        // Build therapist public URL: /pro/{slug}
        $therapistPublicUrl = null;
        if ($therapist) {
            // adapt this if your User model uses another field
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

}
