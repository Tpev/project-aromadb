<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomation;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferJourneyAutomationBuilder
{
    public function createV1Draft(OfferJourney $journey, User $user): OfferJourneyAutomation
    {
        return DB::transaction(function () use ($journey, $user) {
            $automation = $journey->automations()->firstOrCreate([
                'name' => 'Suivi du parcours',
            ], [
                'user_id' => $user->id,
                'status' => 'draft',
                'trigger_type' => 'lead_captured',
                'reentry_mode' => 'once',
                'quiet_hours_start' => config('offer_journeys.quiet_hours.start', '20:00'),
                'quiet_hours_end' => config('offer_journeys.quiet_hours.end', '08:00'),
            ]);

            if ($automation->versions()->doesntExist()) {
                $version = $automation->versions()->create([
                    'version_number' => 1,
                    'status' => 'draft',
                    'definition_json' => ['schema_version' => 1],
                ]);
                $version->nodes()->createMany($this->v1Nodes($journey));
            }

            return $automation;
        });
    }

    public function editableVersion(OfferJourneyAutomation $automation): OfferJourneyAutomationVersion
    {
        $draft = $automation->versions()->where('status', 'draft')->latest('version_number')->first();

        if ($draft) {
            return $draft->load('nodes');
        }

        $source = $automation->publishedVersion()->with('nodes')->firstOrFail();

        return DB::transaction(function () use ($automation, $source) {
            $version = $automation->versions()->create([
                'version_number' => ((int) $automation->versions()->max('version_number')) + 1,
                'status' => 'draft',
                'definition_json' => $source->definition_json,
            ]);

            foreach ($source->nodes as $node) {
                $version->nodes()->create($node->only([
                    'node_key', 'type', 'name', 'config_json', 'next_node_key', 'yes_node_key',
                    'no_node_key', 'position_x', 'position_y',
                ]));
            }

            return $version->load('nodes');
        });
    }

    public function publish(OfferJourneyAutomation $automation, OfferJourneyAutomationVersion $version, User $publisher): void
    {
        abort_unless((int) $version->offer_journey_automation_id === (int) $automation->id, 404);
        $version->load('nodes');

        if ($version->status !== 'draft') {
            throw ValidationException::withMessages(['automation' => 'Cette version est déjà publiée.']);
        }

        if ($version->nodes->isEmpty() || $version->nodes->count() > 20) {
            throw ValidationException::withMessages(['automation' => 'La séquence doit contenir entre une et vingt étapes.']);
        }
        $enabled = $version->nodes->where('type', 'email')->filter(fn ($node) => ($node->config_json['is_enabled'] ?? false) === true);
        if ($enabled->count() > config('offer_journeys.limits.v1_message_steps', 3)) {
            throw ValidationException::withMessages(['automation' => 'Activez au maximum trois messages par parcours.']);
        }

        foreach ($enabled as $node) {
            if (blank($node->config_json['subject'] ?? null) || blank($node->config_json['body'] ?? null)) {
                throw ValidationException::withMessages(['automation' => 'Chaque message activé doit avoir un objet et un contenu.']);
            }
        }

        DB::transaction(function () use ($automation, $version, $publisher) {
            $automation->versions()->where('status', 'published')->update(['status' => 'superseded']);
            $version->update([
                'status' => 'published',
                'published_by_user_id' => $publisher->id,
                'published_at' => now(),
                'definition_json' => [
                    'schema_version' => 1,
                    'nodes' => $version->nodes->map(fn ($node) => [
                        'node_key' => $node->node_key,
                        'type' => $node->type,
                        'name' => $node->name,
                        'config' => $node->config_json,
                        'next_node_key' => $node->next_node_key,
                    ])->values()->all(),
                ],
            ]);
            $automation->update([
                'status' => 'active',
                'published_version_id' => $version->id,
                'published_at' => now(),
                'paused_at' => null,
            ]);
        });
    }

    private function v1Nodes(OfferJourney $journey): array
    {
        $first = $journey->objective === 'lead_magnet'
            ? ['Votre ressource : {{offre}}', "Bonjour {{prenom}},\n\nVoici la ressource demandée : {{lien_ressource}}\n\nBonne découverte,\n{{nom_praticien}}"]
            : ['Votre demande concernant {{offre}}', "Bonjour {{prenom}},\n\nVotre demande a bien été reçue. Je reviendrai vers vous dès que possible.\n\n{{nom_praticien}}"];

        return [
            [
                'node_key' => 'message_1', 'type' => 'email', 'name' => 'Confirmation immédiate',
                'config_json' => ['delay_minutes' => 0, 'category' => 'transactional', 'subject' => $first[0], 'body' => $first[1], 'is_enabled' => true],
                'next_node_key' => 'message_2', 'position_x' => 0, 'position_y' => 0,
            ],
            [
                'node_key' => 'message_2', 'type' => 'email', 'name' => 'Conseil complémentaire',
                'config_json' => ['delay_minutes' => 2880, 'category' => 'marketing', 'subject' => 'Un conseil pour aller plus loin', 'body' => "Bonjour {{prenom}},\n\nJ’espère que cette première ressource vous a été utile. Vous pouvez retrouver mon offre ici : {{lien_offre}}\n\n{{nom_praticien}}", 'is_enabled' => true],
                'next_node_key' => 'message_3', 'position_x' => 0, 'position_y' => 160,
            ],
            [
                'node_key' => 'message_3', 'type' => 'email', 'name' => 'Prochaine étape',
                'config_json' => ['delay_minutes' => 7200, 'category' => 'marketing', 'subject' => 'Souhaitez-vous en parler ?', 'body' => "Bonjour {{prenom}},\n\nSi vous souhaitez avancer, vous pouvez découvrir la prochaine étape proposée ici : {{lien_offre}}\n\n{{nom_praticien}}", 'is_enabled' => true],
                'next_node_key' => null, 'position_x' => 0, 'position_y' => 320,
            ],
        ];
    }
}
