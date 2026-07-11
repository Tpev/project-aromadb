<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use App\Domain\OfferJourneys\Models\OfferJourneySlugRedirect;
use App\Http\Controllers\Controller;
use App\Domain\OfferJourneys\Services\OfferJourneyTransitionEditor;
use App\Domain\OfferJourneys\Services\OfferJourneyResourceStorage;
use App\Domain\OfferJourneys\Models\OfferJourneyReusableSection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferJourneyPageController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, OfferJourney $journey): RedirectResponse
    {
        $this->authorize('update', $journey);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'type' => ['required', Rule::in(config('offer_journeys.allowed_page_types', []))],
        ]);

        $baseSlug = Str::slug($validated['name']) ?: 'etape';
        $slug = $baseSlug;
        $counter = 2;

        while ($journey->pages()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $page = $journey->pages()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'type' => $validated['type'],
            'position' => ((int) $journey->pages()->max('position')) + 1,
            'draft_content_json' => [
                'title' => $validated['name'],
                'summary' => '',
                'cta_label' => $validated['type'] === 'thank_you' ? 'Voir le profil du praticien' : 'Continuer',
                'audience' => '',
                'outcomes' => [],
                'steps' => [],
                'practical_details' => '',
                'faq' => [],
            ],
            'validation_state' => 'incomplete',
        ]);

        return redirect()->route('offer-journeys.pages.edit', [$journey, $page])
            ->with('success', 'La nouvelle étape a été ajoutée.');
    }

    public function edit(OfferJourney $journey, OfferJourneyPage $page): View
    {
        $this->authorizePage($journey, $page);

        $journey->load(['pages.form.fields', 'transitions']);
        $page->load('form.fields');
        return view('offer-journeys.practitioner.pages.edit', [
            'journey' => $journey,
            'page' => $page,
            'content' => $page->draft_content_json ?? [],
            'primaryTransition' => $journey->transitions->first(fn ($transition) => $transition->from_page_id === $page->id && ! $transition->is_fallback),
            'fallbackTransition' => $journey->transitions->first(fn ($transition) => $transition->from_page_id === $page->id && $transition->is_fallback),
            'form' => $page->form,
            'customFields' => $page->form?->fields
                ->filter(fn ($field): bool => str_starts_with($field->name, 'custom_'))
                ->values() ?? collect(),
            'reusableSections' => (bool) config('offer_journeys.rich_editor_enabled', false)
                ? OfferJourneyReusableSection::query()->where('user_id', $journey->user_id)->orderBy('name')->get()
                : collect(),
        ]);
    }

    public function update(Request $request, OfferJourney $journey, OfferJourneyPage $page, OfferJourneyTransitionEditor $transitionEditor, OfferJourneyResourceStorage $resourceStorage): RedirectResponse
    {
        $this->authorizePage($journey, $page);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'alpha_dash',
                'max:120',
                Rule::unique('offer_journey_pages')->where('offer_journey_id', $journey->id)->ignore($page->id),
            ],
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:1600'],
            'cta_label' => ['required', 'string', 'max:80'],
            'audience' => ['nullable', 'string', 'max:2000'],
            'outcomes' => ['nullable', 'string', 'max:4000'],
            'steps' => ['nullable', 'string', 'max:4000'],
            'practical_details' => ['nullable', 'string', 'max:2000'],
            'faq' => ['nullable', 'string', 'max:6000'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'seo_description' => ['nullable', 'string', 'max:300'],
            'is_indexable' => ['nullable', 'boolean'],
            'resource_url' => ['nullable', 'url:http,https', 'max:2000'],
            'resource_file_upload' => ['nullable', 'file', 'max:51200', 'mimetypes:application/pdf,application/zip,audio/mpeg,audio/mp4,audio/x-m4a,audio/wav,video/mp4,video/webm'],
            'remove_resource_file' => ['nullable', 'boolean'],
            'transition_action' => ['required', Rule::in(['none', 'next_page', 'source'])],
            'transition_page_id' => ['nullable', 'integer'],
            'transition_condition' => ['required', Rule::in(['always', 'marketing_consent'])],
            'fallback_page_id' => ['nullable', 'integer'],
            'form_fields' => ['nullable', 'array'],
            'form_fields.*' => [Rule::in(['first_name', 'last_name', 'email', 'phone', 'contact_preference', 'city', 'postal_code'])],
            'form_submit_label' => ['nullable', 'string', 'max:80'],
            'form_privacy_text' => ['nullable', 'string', 'max:1000'],
            'marketing_consent_mode' => ['nullable', Rule::in(['disabled', 'optional'])],
            'custom_fields' => ['nullable', 'array', 'max:3'],
            'custom_fields.*.label' => ['nullable', 'string', 'max:120'],
            'custom_fields.*.type' => ['nullable', Rule::in(['short_text', 'single_choice', 'multiple_choice'])],
            'custom_fields.*.options' => ['nullable', 'string', 'max:2000'],
            'custom_fields.*.purpose' => ['nullable', 'string', 'max:255'],
            'custom_fields.*.is_required' => ['nullable', 'boolean'],
            'custom_fields.*.condition_field' => ['nullable', 'string', 'max:80'],
            'custom_fields.*.condition_value' => ['nullable', 'string', 'max:120'],
            'enabled_blocks' => ['nullable', 'array'],
            'enabled_blocks.*' => [Rule::in(['audience', 'outcomes', 'steps', 'hero_image', 'gallery', 'video', 'testimonials', 'speaker', 'price', 'practical', 'faq'])],
            'block_order' => ['nullable', 'array'],
            'block_order.*' => [Rule::in(['audience', 'outcomes', 'steps', 'hero_image', 'gallery', 'video', 'testimonials', 'speaker', 'price', 'practical', 'faq'])],
            'hero_image_url' => ['nullable', 'url:http,https', 'max:2000'],
            'hero_image_alt' => ['nullable', 'string', 'max:180'],
            'gallery_items' => ['nullable', 'string', 'max:6000'],
            'video_url' => ['nullable', 'url:http,https', 'max:2000'],
            'testimonials' => ['nullable', 'string', 'max:6000'],
            'speaker_name' => ['nullable', 'string', 'max:160'],
            'speaker_title' => ['nullable', 'string', 'max:200'],
            'speaker_bio' => ['nullable', 'string', 'max:2000'],
            'speaker_image_url' => ['nullable', 'url:http,https', 'max:2000'],
            'price_label' => ['nullable', 'string', 'max:120'],
            'theme_style' => ['nullable', Rule::in(['olive', 'forest', 'clay', 'neutral'])],
        ]);

        $customFields = collect();
        if ((bool) config('offer_journeys.custom_forms_enabled', false)) {
            $customFields = collect($validated['custom_fields'] ?? [])
                ->filter(fn (array $field): bool => filled($field['label'] ?? null))
                ->take(3)
                ->values();
            foreach ($customFields as $customPosition => $customField) {
                if (blank($customField['purpose'] ?? null)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "custom_fields.{$customPosition}.purpose" => 'Indiquez pourquoi cette question est utile.',
                    ]);
                }
                if (in_array($customField['type'] ?? null, ['single_choice', 'multiple_choice'], true)
                    && count($this->lines($customField['options'] ?? '')) < 2) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "custom_fields.{$customPosition}.options" => 'Ajoutez au moins deux choix, un par ligne.',
                    ]);
                }
            }
        }
        if ((bool) config('offer_journeys.rich_editor_enabled', false)) {
            if (filled($validated['hero_image_url'] ?? null) && blank($validated['hero_image_alt'] ?? null)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'hero_image_alt' => 'Décrivez brièvement l image principale pour les personnes utilisant un lecteur d écran.',
                ]);
            }
            foreach ($this->pairedLines($validated['gallery_items'] ?? '', 'url', 'alt') as $item) {
                if (! filter_var($item['url'], FILTER_VALIDATE_URL)) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'gallery_items' => 'Chaque image de la galerie doit utiliser une adresse HTTPS valide.',
                    ]);
                }
            }
        }

        $existingResource = ($page->draft_content_json ?? [])['resource_file'] ?? null;
        $resourceFile = $request->boolean('remove_resource_file') ? null : $existingResource;
        if ($request->hasFile('resource_file_upload')) {
            $resourceFile = $resourceStorage->store($request->file('resource_file_upload'), $request->user(), $journey);
        }

        $oldSlug = $page->slug;
        $newSlug = Str::slug($validated['slug']);
        $blocks = (bool) config('offer_journeys.rich_editor_enabled', false)
            ? $this->blocks($validated)
            : (($page->draft_content_json ?? [])['blocks'] ?? []);

        $page->update([
            'name' => $validated['name'],
            'slug' => $newSlug,
            'draft_content_json' => [
                'title' => $validated['title'],
                'summary' => $validated['summary'] ?? '',
                'cta_label' => $validated['cta_label'],
                'audience' => $validated['audience'] ?? '',
                'outcomes' => $this->lines($validated['outcomes'] ?? ''),
                'steps' => $this->lines($validated['steps'] ?? ''),
                'practical_details' => $validated['practical_details'] ?? '',
                'faq' => $this->faq($validated['faq'] ?? ''),
                'resource_url' => $validated['resource_url'] ?? null,
                'resource_file' => $resourceFile,
                'blocks' => $blocks,
            ],
            'theme_json' => ['style' => $validated['theme_style'] ?? data_get($page->theme_json, 'style', 'olive')],
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'is_indexable' => $request->boolean('is_indexable'),
            'validation_state' => 'ready',
        ]);
        if ($oldSlug !== $newSlug) {
            OfferJourneySlugRedirect::query()->updateOrCreate(
                ['offer_journey_id' => $journey->id, 'scope_type' => 'page', 'old_slug' => $oldSlug],
                ['offer_journey_page_id' => $page->id, 'new_slug' => $newSlug]
            );
        }
        $transitionEditor->update($journey, $page, $validated);

        if ($page->form) {
            $selectedFields = collect($validated['form_fields'] ?? [])->push('email')->unique();
            $definitions = [
                'first_name' => ['label' => 'Prénom', 'type' => 'text', 'required' => true, 'purpose' => 'identifier le contact'],
                'last_name' => ['label' => 'Nom', 'type' => 'text', 'required' => false, 'purpose' => 'identifier le contact'],
                'email' => ['label' => 'Adresse email', 'type' => 'email', 'required' => true, 'purpose' => 'répondre à la demande'],
                'phone' => ['label' => 'Téléphone', 'type' => 'tel', 'required' => false, 'purpose' => 'recontacter si demandé'],
                'contact_preference' => ['label' => 'Préférence de contact', 'type' => 'text', 'required' => false, 'purpose' => 'respecter le canal demandé'],
                'city' => ['label' => 'Ville', 'type' => 'text', 'required' => false, 'purpose' => 'situer la demande'],
                'postal_code' => ['label' => 'Code postal', 'type' => 'text', 'required' => false, 'purpose' => 'situer la demande'],
            ];
            $page->form->update([
                'submit_label' => $validated['form_submit_label'] ?: $validated['cta_label'],
                'privacy_text' => $validated['form_privacy_text'] ?: 'Vos informations sont utilisées uniquement pour répondre à cette demande.',
                'marketing_consent_mode' => $validated['marketing_consent_mode'] ?? 'optional',
            ]);
            $page->form->fields()->delete();
            foreach ($selectedFields->values() as $position => $fieldName) {
                $definition = $definitions[$fieldName];
                $page->form->fields()->create([
                    'name' => $fieldName, 'label' => $definition['label'], 'type' => $definition['type'],
                    'is_required' => $definition['required'], 'position' => $position, 'purpose' => $definition['purpose'],
                ]);
            }

            if ((bool) config('offer_journeys.custom_forms_enabled', false)) {
                foreach ($customFields as $customPosition => $customField) {
                    $type = $customField['type'] ?? 'short_text';
                    $options = in_array($type, ['single_choice', 'multiple_choice'], true)
                        ? $this->lines($customField['options'] ?? '')
                        : [];
                    $page->form->fields()->create([
                        'name' => 'custom_'.($customPosition + 1).'_'.Str::limit(Str::slug($customField['label'], '_'), 45, ''),
                        'label' => $customField['label'],
                        'type' => match ($type) {
                            'single_choice' => 'select',
                            'multiple_choice' => 'multiselect',
                            default => 'text',
                        },
                        'is_required' => (bool) ($customField['is_required'] ?? false),
                        'options_json' => array_filter([
                            'options' => $options,
                            'show_if' => filled($customField['condition_field'] ?? null) && filled($customField['condition_value'] ?? null)
                                ? ['field' => $customField['condition_field'], 'value' => $customField['condition_value']]
                                : null,
                        ]),
                        'position' => $selectedFields->count() + $customPosition,
                        'purpose' => $customField['purpose'],
                    ]);
                }
            }
        }

        return back()->with('success', 'Le brouillon de la page a été enregistré.');
    }

    public function destroy(OfferJourney $journey, OfferJourneyPage $page): RedirectResponse
    {
        $this->authorizePage($journey, $page);
        abort_if($journey->pages()->count() <= 1, 422, 'Un parcours doit conserver au moins une page.');

        $page->delete();

        return redirect()->route('offer-journeys.show', $journey)->with('success', 'La page a été supprimée du brouillon.');
    }

    public function move(Request $request, OfferJourney $journey, OfferJourneyPage $page): RedirectResponse
    {
        $this->authorizePage($journey, $page);
        $validated = $request->validate(['direction' => ['required', Rule::in(['up', 'down'])]]);

        $comparison = $validated['direction'] === 'up' ? '<' : '>';
        $order = $validated['direction'] === 'up' ? 'desc' : 'asc';
        $sibling = $journey->pages()
            ->where('position', $comparison, $page->position)
            ->orderBy('position', $order)
            ->first();

        if ($sibling) {
            [$pagePosition, $siblingPosition] = [$page->position, $sibling->position];
            $page->update(['position' => $siblingPosition]);
            $sibling->update(['position' => $pagePosition]);
        }

        return back();
    }

    private function authorizePage(OfferJourney $journey, OfferJourneyPage $page): void
    {
        $this->authorize('update', $journey);
        abort_unless((int) $page->offer_journey_id === (int) $journey->id, 404);
    }

    private function lines(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function faq(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(function ($line) {
                $parts = array_map('trim', explode('|', $line, 2));

                return count($parts) === 2 && $parts[0] !== '' && $parts[1] !== ''
                    ? ['question' => $parts[0], 'answer' => $parts[1]]
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    private function blocks(array $validated): array
    {
        $enabled = collect($validated['enabled_blocks'] ?? [])->unique();
        $order = collect($validated['block_order'] ?? [])->filter(fn (string $type): bool => $enabled->contains($type));
        $order = $order->merge($enabled->diff($order))->unique()->values();
        $data = [
            'audience' => ['text' => $validated['audience'] ?? ''],
            'outcomes' => ['items' => $this->lines($validated['outcomes'] ?? '')],
            'steps' => ['items' => $this->lines($validated['steps'] ?? '')],
            'hero_image' => ['url' => $validated['hero_image_url'] ?? null, 'alt' => $validated['hero_image_alt'] ?? null],
            'gallery' => ['items' => $this->pairedLines($validated['gallery_items'] ?? '', 'url', 'alt')],
            'video' => ['url' => $validated['video_url'] ?? null],
            'testimonials' => ['items' => $this->pairedLines($validated['testimonials'] ?? '', 'author', 'quote')],
            'speaker' => ['name' => $validated['speaker_name'] ?? null, 'title' => $validated['speaker_title'] ?? null, 'bio' => $validated['speaker_bio'] ?? null, 'image_url' => $validated['speaker_image_url'] ?? null],
            'price' => ['label' => $validated['price_label'] ?? null],
            'practical' => ['text' => $validated['practical_details'] ?? ''],
            'faq' => ['items' => $this->faq($validated['faq'] ?? '')],
        ];

        return $order->map(fn (string $type, int $position): array => [
            'id' => $type,
            'type' => $type,
            'position' => $position,
            'data' => $data[$type],
        ])->values()->all();
    }

    private function pairedLines(string $value, string $firstKey, string $secondKey): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value))
            ->map(function (string $line) use ($firstKey, $secondKey): ?array {
                $parts = array_map('trim', explode('|', $line, 2));

                return count($parts) === 2 && $parts[0] !== '' && $parts[1] !== ''
                    ? [$firstKey => $parts[0], $secondKey => $parts[1]]
                    : null;
            })
            ->filter()
            ->values()
            ->all();
    }
}
