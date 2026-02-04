<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesignTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignTemplateController extends Controller
{
    private function assertAdmin(): void
    {
        abort_unless(auth()->check() && (bool) auth()->user()->is_admin, 403);
    }

    public function index()
    {
        $this->assertAdmin();

        $templates = DesignTemplate::orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('admin.design-templates.index', compact('templates'));
    }

    public function create(Request $request)
    {
        $this->assertAdmin();

        // Open the same editor, but in admin mode
        return view('tools.konva-editor', [
            'adminMode' => true,
            'adminEditingTemplate' => null,
            'templatesDb' => collect(), // admin doesn't need the user list here
            'events' => collect(),
        ]);
    }

    public function edit(DesignTemplate $template)
    {
        $this->assertAdmin();

        return view('tools.konva-editor', [
            'adminMode' => true,
            'adminEditingTemplate' => [
                'id' => $template->id,
                'name' => $template->name,
                'category' => $template->category,
                'format_id' => $template->format_id,
                'konva_json' => $template->konva_json,
                'preview_url' => $template->previewUrl(),
                'is_active' => $template->is_active,
                'sort_order' => $template->sort_order,
            ],
            'templatesDb' => collect(),
            'events' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'category' => ['required','string','max:50'],
            'format_id' => ['required','string','max:50'],
            'konva_json' => ['required','string'],
            'preview_base64' => ['nullable','string'],
            'is_active' => ['nullable','boolean'],
        ]);

        $tpl = new DesignTemplate();
        $tpl->name = $data['name'];
        $tpl->category = $data['category'];
        $tpl->format_id = $data['format_id'];
        $tpl->konva_json = $data['konva_json'];
        $tpl->is_active = (bool) ($data['is_active'] ?? true);
        $tpl->created_by = auth()->id();
        $tpl->sort_order = (DesignTemplate::max('sort_order') ?? 0) + 1;

        if (!empty($data['preview_base64'])) {
            $tpl->preview_path = $this->storePreviewBase64($data['preview_base64']);
        }

        $tpl->save();

        return response()->json([
            'ok' => true,
            'id' => $tpl->id,
            'preview_url' => $tpl->previewUrl(),
        ]);
    }

    public function update(Request $request, DesignTemplate $template)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'category' => ['required','string','max:50'],
            'format_id' => ['required','string','max:50'],
            'konva_json' => ['required','string'],
            'preview_base64' => ['nullable','string'],
            'is_active' => ['nullable','boolean'],
            'sort_order' => ['nullable','integer','min:0'],
        ]);

        $template->name = $data['name'];
        $template->category = $data['category'];
        $template->format_id = $data['format_id'];
        $template->konva_json = $data['konva_json'];
        if (isset($data['is_active'])) $template->is_active = (bool) $data['is_active'];
        if (isset($data['sort_order'])) $template->sort_order = (int) $data['sort_order'];

        if (!empty($data['preview_base64'])) {
            // delete old
            if ($template->preview_path) {
                Storage::disk('public')->delete($template->preview_path);
            }
            $template->preview_path = $this->storePreviewBase64($data['preview_base64']);
        }

        $template->save();

        return response()->json([
            'ok' => true,
            'id' => $template->id,
            'preview_url' => $template->previewUrl(),
        ]);
    }

    public function toggle(DesignTemplate $template)
    {
        $this->assertAdmin();

        $template->is_active = !$template->is_active;
        $template->save();

        return redirect()->route('admin.design-templates.index');
    }

    public function reorder(Request $request)
    {
        $this->assertAdmin();

        $data = $request->validate([
            'ids' => ['required','array'],
            'ids.*' => ['integer'],
        ]);

        $i = 1;
        foreach ($data['ids'] as $id) {
            DesignTemplate::where('id', $id)->update(['sort_order' => $i]);
            $i++;
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(DesignTemplate $template)
    {
        $this->assertAdmin();

        if ($template->preview_path) {
            Storage::disk('public')->delete($template->preview_path);
        }

        $template->delete();

        return redirect()->route('admin.design-templates.index');
    }

    private function storePreviewBase64(string $base64): string
    {
        // Expect: data:image/png;base64,....
        if (!str_contains($base64, 'base64,')) {
            // still try to store as raw base64
            $raw = base64_decode($base64);
            $path = 'design-templates/' . uniqid('tpl_', true) . '.png';
            Storage::disk('public')->put($path, $raw);
            return $path;
        }

        [$meta, $payload] = explode('base64,', $base64, 2);
        $raw = base64_decode($payload);

        $ext = 'png';
        if (str_contains($meta, 'image/webp')) $ext = 'webp';
        if (str_contains($meta, 'image/jpeg')) $ext = 'jpg';

        $path = 'design-templates/' . uniqid('tpl_', true) . '.' . $ext;
        Storage::disk('public')->put($path, $raw);

        return $path;
    }
}
