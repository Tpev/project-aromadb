<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DesignTemplate;
use Illuminate\Http\Request;

class DesignTemplateController extends Controller
{
    public function index(Request $request)
    {
        $q = DesignTemplate::query()->active()->orderBy('sort_order')->orderByDesc('id');

        if ($request->filled('format_id')) {
            $q->where('format_id', $request->string('format_id'));
        }

        if ($request->filled('category')) {
            $q->where('category', $request->string('category'));
        }

        $items = $q->get()->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'category' => $t->category,
                'format_id' => $t->format_id,
                'preview_url' => $t->previewUrl(),
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function show(DesignTemplate $template)
    {
        abort_unless($template->is_active, 404);

        return response()->json([
            'id' => $template->id,
            'name' => $template->name,
            'category' => $template->category,
            'format_id' => $template->format_id,
            'konva_json' => $template->konva_json,
            'preview_url' => $template->previewUrl(),
        ]);
    }
}
