<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferJourneyPublisher
{
    public function __construct(private readonly OfferJourneyPublicationPreflight $preflight)
    {
    }

    public function publish(OfferJourney $journey, User $publisher): OfferJourneyVersion
    {
        return DB::transaction(function () use ($journey, $publisher) {
            $journey->load(['pages.form.fields', 'transitions']);
            if ((bool) config('offer_journeys.publication_assistance_enabled', false)) {
                $this->preflight->assertPublishable($journey);
            } else {
                $this->validateForPublication($journey);
            }

            $nextVersion = ((int) $journey->versions()->max('version_number')) + 1;

            $version = $journey->versions()->create([
                'version_number' => $nextVersion,
                'published_by_user_id' => $publisher->id,
                'schema_version' => 1,
                'snapshot_json' => [
                    'name' => $journey->name,
                    'slug' => $journey->slug,
                    'objective' => $journey->objective,
                    'source_type' => $journey->source_type,
                    'source_id' => $journey->source_id,
                    'primary_conversion_type' => $journey->primary_conversion_type,
                    'timezone' => $journey->timezone,
                    'transitions' => $journey->transitions->map->only([
                        'from_page_id',
                        'to_page_id',
                        'trigger',
                        'condition_json',
                        'external_action',
                        'priority',
                        'is_fallback',
                        'is_active',
                    ])->values()->all(),
                ],
                'published_at' => now(),
            ]);

            foreach ($journey->pages as $page) {
                $content = $page->draft_content_json ?? [];

                if ($page->form) {
                    $content['_form'] = [
                        'submit_label' => $page->form->submit_label,
                        'success_message' => $page->form->success_message,
                        'privacy_text' => $page->form->privacy_text,
                        'marketing_consent_mode' => $page->form->marketing_consent_mode,
                        'fields' => $page->form->fields->map->only([
                            'name',
                            'label',
                            'type',
                            'is_required',
                            'options_json',
                            'position',
                            'purpose',
                        ])->values()->all(),
                    ];
                }

                $version->pages()->create([
                    'offer_journey_page_id' => $page->id,
                    'slug' => $page->slug,
                    'type' => $page->type,
                    'position' => $page->position,
                    'schema_version' => 1,
                    'content_json' => $content,
                    'theme_json' => $page->theme_json,
                    'seo_title' => $page->seo_title,
                    'seo_description' => $page->seo_description,
                    'is_indexable' => $page->is_indexable,
                    'content_hash' => hash('sha256', json_encode($content, JSON_UNESCAPED_UNICODE)),
                ]);
            }

            $journey->forceFill([
                'published_version_id' => $version->id,
                'status' => 'published',
                'published_at' => now(),
                'paused_at' => null,
                'archived_at' => null,
            ])->save();

            return $version;
        });
    }

    public function restore(OfferJourney $journey, OfferJourneyVersion $version): void
    {
        abort_unless((int) $version->offer_journey_id === (int) $journey->id, 404);

        $journey->forceFill([
            'published_version_id' => $version->id,
            'status' => 'published',
            'published_at' => now(),
            'paused_at' => null,
        ])->save();
    }

    private function validateForPublication(OfferJourney $journey): void
    {
        $errors = [];

        if ($journey->pages->isEmpty()) {
            $errors['pages'] = 'Ajoutez au moins une page avant de publier.';
        }

        $firstPage = $journey->pages->sortBy('position')->first();
        $content = $firstPage?->draft_content_json ?? [];

        if (blank($content['title'] ?? null)) {
            $errors['title'] = 'Le titre public de la première page est requis.';
        }

        if (blank($content['cta_label'] ?? null) && $firstPage?->type !== 'thank_you') {
            $errors['cta_label'] = 'L’action principale de la première page est requise.';
        }

        if ($journey->objective === 'lead_magnet' && ! $journey->pages->contains(function ($page) {
            $content = $page->draft_content_json ?? [];

            return filled($content['resource_url'] ?? null)
                || app(OfferJourneyResourceStorage::class)->exists($content['resource_file'] ?? null);
        })) {
            $errors['resource'] = 'Ajoutez une ressource disponible avant de publier.';
        }

        if (in_array($journey->objective, ['appointment', 'event', 'training', 'gift_voucher'], true)
            && ! app(OfferJourneySourceResolver::class)->sourceAvailable($journey, $journey->user)) {
            $errors['source'] = 'Reliez une ressource Olithea disponible avant de publier.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
