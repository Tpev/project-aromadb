<?php

namespace App\Http\Controllers;

use App\Models\DesignTemplate;
use App\Models\DigitalTraining;
use App\Models\Event;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Throwable;

class KonvaEditorController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        $events = Event::query()
            ->where('user_id', $user->id)
            ->orderBy('start_date_time', 'asc')
            ->get([
                'id',
                'name',
                'description',
                'start_date_time',
                'location',
                'price',
            ]);

        $testimonialsQuery = Testimonial::query()
            ->where('therapist_id', $user->id)
            ->with(['clientProfile:id,first_name,last_name'])
            ->latest('id');

        if (Schema::hasColumn('testimonials', 'visible_on_public_profile')) {
            $testimonialsQuery->where(function ($q) {
                $q->whereNull('visible_on_public_profile')
                    ->orWhere('visible_on_public_profile', true);
            });
        }

        $testimonials = $testimonialsQuery
            ->take(80)
            ->get([
                'id',
                'client_profile_id',
                'testimonial',
                'rating',
                'reviewer_name',
            ]);

        $products = Product::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'description',
                'price',
                'duration',
            ]);

        $trainings = DigitalTraining::query()
            ->where('user_id', $user->id)
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'description',
                'price_cents',
                'estimated_duration_minutes',
                'is_free',
            ]);

        $templatesDb = DesignTemplate::query()
            ->active()
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category,
                    'format_id' => $template->format_id,
                    'preview_url' => $template->previewUrl(),
                ];
            });

        $offers = $products->map(function ($product) {
            return [
                'id' => 'product:' . $product->id,
                'type' => 'product',
                'name' => $product->name,
                'description' => (string) ($product->description ?? ''),
                'price_label' => is_null($product->price) ? null : number_format((float) $product->price, 2, ',', ' ') . ' €',
                'duration_minutes' => (int) ($product->duration ?? 0),
            ];
        })->values();

        $trainingOffers = $trainings->map(function ($training) {
            $isFree = (bool) ($training->is_free ?? false);
            $priceLabel = null;
            if (!$isFree && !is_null($training->price_cents)) {
                $priceLabel = number_format(((int) $training->price_cents) / 100, 2, ',', ' ') . ' €';
            }

            return [
                'id' => 'training:' . $training->id,
                'type' => 'training',
                'name' => $training->title,
                'description' => (string) ($training->description ?? ''),
                'price_label' => $isFree ? 'Gratuit' : $priceLabel,
                'duration_minutes' => (int) ($training->estimated_duration_minutes ?? 0),
            ];
        })->values();

        $konvaContext = [
            'events' => $events->map(function ($event) {
                $dateIso = $event->start_date_time ? Carbon::parse($event->start_date_time)->toIso8601String() : null;

                return [
                    'id' => (string) $event->id,
                    'name' => (string) ($event->name ?? ('Evenement #' . $event->id)),
                    'description' => (string) ($event->description ?? ''),
                    'date_iso' => $dateIso,
                    'date_label' => $event->start_date_time
                        ? Carbon::parse($event->start_date_time)->locale('fr_FR')->translatedFormat('d M Y - H\\hi')
                        : '',
                    'location' => (string) ($event->location ?? ''),
                    'price_label' => is_null($event->price) ? null : number_format((float) $event->price, 2, ',', ' ') . ' €',
                ];
            })->values(),
            'testimonials' => $testimonials->map(function ($testimonial) {
                $fallbackName = trim(
                    (string) optional($testimonial->clientProfile)->first_name . ' ' .
                    (string) optional($testimonial->clientProfile)->last_name
                );
                $reviewer = trim((string) ($testimonial->reviewer_name ?: $fallbackName));

                return [
                    'id' => (string) $testimonial->id,
                    'reviewer_name' => $reviewer !== '' ? $reviewer : 'Client',
                    'rating' => (int) ($testimonial->rating ?? 5),
                    'testimonial' => (string) ($testimonial->testimonial ?? ''),
                ];
            })->values(),
            'offers' => $offers->concat($trainingOffers)->values(),
        ];

        return view('tools.konva-editor', [
            'events' => $events,
            'templatesDb' => $templatesDb,
            'konvaTemplates' => config('konva.templates', []),
            'konvaBranding' => $this->resolveBrandingSettings($user),
            'konvaBrandingPresets' => config('konva.branding_presets', []),
            'konvaBrandingFonts' => config('konva.branding_fonts', []),
            'konvaContext' => $konvaContext,
        ]);
    }

    public function updateBranding(Request $request): JsonResponse
    {
        $data = $request->validate([
            'preset' => ['nullable', 'string', 'max:50'],
            'font_heading' => ['required', 'string', 'max:120'],
            'font_body' => ['required', 'string', 'max:120'],
            'color_primary' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_secondary' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_accent' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_background' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'color_text' => ['required', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $settings = [
            'preset' => $data['preset'] ?: null,
            'fonts' => [
                'heading' => $data['font_heading'],
                'body' => $data['font_body'],
            ],
            'colors' => [
                'primary' => strtoupper($data['color_primary']),
                'secondary' => strtoupper($data['color_secondary']),
                'accent' => strtoupper($data['color_accent']),
                'background' => strtoupper($data['color_background']),
                'text' => strtoupper($data['color_text']),
            ],
            'updated_at' => now()->toIso8601String(),
        ];

        $user = $request->user();

        if (!Schema::hasColumn('users', 'konva_branding_settings')) {
            return response()->json([
                'ok' => false,
                'message' => 'Sauvegarde branding indisponible: migration non appliquee.',
                'settings' => $this->resolveBrandingSettings($user),
            ], 503);
        }

        try {
            $user->konva_branding_settings = $settings;
            $user->save();
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Echec de sauvegarde temporaire.',
            ], 500);
        }

        return response()->json([
            'ok' => true,
            'settings' => $this->resolveBrandingSettings($user),
        ]);
    }

    private function resolveBrandingSettings($user): array
    {
        $defaults = [
            'preset' => 'zen_olive',
            'fonts' => [
                'heading' => 'poppins',
                'body' => 'inter',
            ],
            'colors' => [
                'primary' => '#647A0B',
                'secondary' => '#854F38',
                'accent' => '#D4A373',
                'background' => '#F8F9F5',
                'text' => '#1F2937',
            ],
        ];

        $saved = $user->konva_branding_settings;
        if (!is_array($saved)) {
            return $defaults;
        }

        return [
            'preset' => $saved['preset'] ?? $defaults['preset'],
            'fonts' => [
                'heading' => $saved['fonts']['heading'] ?? $defaults['fonts']['heading'],
                'body' => $saved['fonts']['body'] ?? $defaults['fonts']['body'],
            ],
            'colors' => [
                'primary' => $saved['colors']['primary'] ?? $defaults['colors']['primary'],
                'secondary' => $saved['colors']['secondary'] ?? $defaults['colors']['secondary'],
                'accent' => $saved['colors']['accent'] ?? $defaults['colors']['accent'],
                'background' => $saved['colors']['background'] ?? $defaults['colors']['background'],
                'text' => $saved['colors']['text'] ?? $defaults['colors']['text'],
            ],
        ];
    }
}
