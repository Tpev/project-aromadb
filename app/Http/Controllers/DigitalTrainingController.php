<?php

namespace App\Http\Controllers;

use App\Models\DigitalTraining;
use App\Models\TrainingModule;
use App\Models\TrainingBlock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DigitalTrainingController extends Controller
{
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

    public function create()
    {
        return view('digital-trainings.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'title'                     => 'required|string|max:255',
            'description'               => 'nullable|string',
            'cover_image'               => 'nullable|image|max:8048',
            'tags'                      => 'nullable|string', // "stress, sommeil"
            'is_free'                   => 'nullable|boolean',
            'price_eur'                 => 'nullable|numeric|min:0',
            'tax_rate'                  => 'nullable|numeric|min:0|max:100',
            'access_type'               => 'required|in:public,private,subscription',
            'status'                    => 'required|in:draft,published,archived',
            'estimated_duration_minutes'=> 'nullable|integer|min:1',
        ]);

        $slugBase = Str::slug($data['title']);
        $slug = $slugBase;
        $i = 1;
        while (DigitalTraining::where('slug', $slug)->exists()) {
            $slug = $slugBase . '-' . $i++;
        }

        $coverPath = null;
        if ($request->hasFile('cover_image')) {
            $coverPath = $request->file('cover_image')->store('digital-trainings/covers', 'public');
        }

        $tags = null;
        if (!empty($data['tags'])) {
            $tags = collect(explode(',', $data['tags']))
                ->map(fn($t) => trim($t))
                ->filter()
                ->values()
                ->toArray();
        }

        $isFree = (bool)($data['is_free'] ?? false);
        $priceCents = null;
        $taxRate = $data['tax_rate'] ?? 0;

        if (!$isFree && isset($data['price_eur'])) {
            $priceCents = (int)round($data['price_eur'] * 100);
        }

        $training = DigitalTraining::create([
            'user_id'                    => $user->id,
            'title'                      => $data['title'],
            'slug'                       => $slug,
            'description'                => $data['description'] ?? null,
            'cover_image_path'           => $coverPath,
            'tags'                       => $tags,
            'is_free'                    => $isFree,
            'price_cents'                => $priceCents,
            'tax_rate'                   => $taxRate,
            'access_type'                => $data['access_type'],
            'status'                     => $data['status'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
        ]);

        return redirect()
            ->route('digital-trainings.builder', $training)
            ->with('success', 'Formation digitale créée. Vous pouvez maintenant ajouter des modules et du contenu.');
    }

    public function edit(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        return view('digital-trainings.edit', [
            'training' => $digitalTraining,
            'tagsString' => $digitalTraining->tags ? implode(', ', $digitalTraining->tags) : '',
        ]);
    }

    public function update(Request $request, DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $data = $request->validate([
            'title'                     => 'required|string|max:255',
            'description'               => 'nullable|string',
            'cover_image'               => 'nullable|image|max:8048',
            'tags'                      => 'nullable|string',
            'is_free'                   => 'nullable|boolean',
            'price_eur'                 => 'nullable|numeric|min:0',
            'tax_rate'                  => 'nullable|numeric|min:0|max:100',
            'access_type'               => 'required|in:public,private,subscription',
            'status'                    => 'required|in:draft,published,archived',
            'estimated_duration_minutes'=> 'nullable|integer|min:1',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($digitalTraining->cover_image_path) {
                Storage::disk('public')->delete($digitalTraining->cover_image_path);
            }
            $digitalTraining->cover_image_path = $request->file('cover_image')
                ->store('digital-trainings/covers', 'public');
        }

        $tags = null;
        if (!empty($data['tags'])) {
            $tags = collect(explode(',', $data['tags']))
                ->map(fn($t) => trim($t))
                ->filter()
                ->values()
                ->toArray();
        }

        $isFree = (bool)($data['is_free'] ?? false);
        $priceCents = null;
        if (!$isFree && isset($data['price_eur'])) {
            $priceCents = (int)round($data['price_eur'] * 100);
        }

        $digitalTraining->update([
            'title'                      => $data['title'],
            'description'                => $data['description'] ?? null,
            'tags'                       => $tags,
            'is_free'                    => $isFree,
            'price_cents'                => $priceCents,
            'tax_rate'                   => $data['tax_rate'] ?? $digitalTraining->tax_rate,
            'access_type'                => $data['access_type'],
            'status'                     => $data['status'],
            'estimated_duration_minutes' => $data['estimated_duration_minutes'] ?? null,
        ]);

        return redirect()
            ->route('digital-trainings.index')
            ->with('success', 'Formation mise à jour.');
    }

    public function destroy(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        // delete cover file
        if ($digitalTraining->cover_image_path) {
            Storage::disk('public')->delete($digitalTraining->cover_image_path);
        }

        $digitalTraining->delete();

        return redirect()
            ->route('digital-trainings.index')
            ->with('success', 'Formation supprimée.');
    }

    /**
     * Builder screen: modules + blocks
     */
    public function builder(DigitalTraining $digitalTraining)
    {
        $this->authorizeOwner($digitalTraining);

        $training = $digitalTraining->load('modules.blocks');

        return view('digital-trainings.builder', compact('training'));
    }

    /* ========== MODULES ========== */

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

    /* ========== BLOCKS ========== */

    public function storeBlock(Request $request, DigitalTraining $digitalTraining, TrainingModule $module)
    {
        $this->authorizeOwner($digitalTraining);
        $this->authorizeModule($digitalTraining, $module);

        $data = $request->validate([
            'type'      => 'required|in:text,video_url,pdf',
            'title'     => 'nullable|string|max:255',
            'content'   => 'nullable|string',
            'file'      => 'nullable|file|mimes:pdf|max:20480',
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
        ]);

        // For simplicity we don't change type/file here
        $block->update([
            'title'   => $data['title'] ?? $block->title,
            'content' => $block->type === 'pdf' ? $block->content : ($data['content'] ?? $block->content),
        ]);

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

    /* ========== Small helpers ========== */

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
	public function preview(DigitalTraining $digitalTraining)
{
    $this->authorizeOwner($digitalTraining);

    $training = $digitalTraining->load('modules.blocks');

    return view('digital-trainings.preview', compact('training'));
}

}
