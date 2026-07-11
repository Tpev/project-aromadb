<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomation;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationNode;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationVersion;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OfferJourneyAutomationNodeController extends Controller
{
    use AuthorizesRequests;

    public function store(Request $request, OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationVersion $version): RedirectResponse
    {
        $this->authorizeVersion($journey, $automation, $version);
        abort_if($version->nodes()->count() >= 20, 422, 'Une séquence peut contenir au maximum 20 étapes.');
        $validated = $request->validate(['type' => ['required', Rule::in(['wait', 'condition', 'action', 'email', 'end'])]]);
        abort_if($validated['type'] === 'email' && $version->nodes()->where('type', 'email')->count() >= 3, 422, 'Trois messages maximum sont autorisés.');
        $last = $version->nodes()->orderByDesc('position_y')->first();
        $node = $version->nodes()->create([
            'node_key' => 'step_'.Str::lower(Str::random(8)),
            'type' => $validated['type'],
            'name' => $this->label($validated['type']),
            'config_json' => $this->defaults($validated['type']),
            'position_x' => 0,
            'position_y' => ((int) $last?->position_y) + 160,
        ]);
        if ($last && ! $last->next_node_key && $last->type !== 'end') {
            $last->update(['next_node_key' => $node->node_key]);
        }

        return back()->with('success', 'L’étape a été ajoutée au brouillon.');
    }

    public function update(Request $request, OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationVersion $version, OfferJourneyAutomationNode $node): RedirectResponse
    {
        $this->authorizeNode($journey, $automation, $version, $node);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'delay_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'condition_type' => ['nullable', Rule::in(['marketing_consent', 'converted', 'has_tag', 'inactive_days'])],
            'condition_value' => ['nullable', 'string', 'max:180'],
            'action_type' => ['nullable', Rule::in(['add_tag', 'set_status', 'create_task'])],
            'action_value' => ['nullable', 'string', 'max:180'],
            'subject' => ['nullable', 'string', 'max:180'],
            'body' => ['nullable', 'string', 'max:6000'],
            'next_node_key' => ['nullable', 'string', 'max:50'],
            'yes_node_key' => ['nullable', 'string', 'max:50'],
            'no_node_key' => ['nullable', 'string', 'max:50'],
        ]);

        foreach (['next_node_key', 'yes_node_key', 'no_node_key'] as $targetField) {
            $target = $validated[$targetField] ?? null;
            if ($target) {
                $targetNode = $version->nodes()->where('node_key', $target)->firstOrFail();
                abort_if($targetNode->position_y <= $node->position_y, 422, 'Une étape ne peut pointer que vers une étape suivante.');
            }
        }

        $config = $node->config_json ?? [];
        $config = match ($node->type) {
            'wait' => ['delay_minutes' => ((int) ($validated['delay_days'] ?? 1)) * 1440, 'relative_delay' => true, 'is_enabled' => true],
            'condition' => ['condition_type' => $validated['condition_type'] ?? 'marketing_consent', 'value' => $validated['condition_value'] ?? null, 'is_enabled' => true],
            'action' => ['action_type' => $validated['action_type'] ?? 'create_task', 'value' => $validated['action_value'] ?? null, 'is_enabled' => true],
            'email' => ['delay_minutes' => ((int) ($validated['delay_days'] ?? 0)) * 1440, 'category' => 'marketing', 'subject' => $validated['subject'] ?? '', 'body' => $validated['body'] ?? '', 'is_enabled' => true],
            default => ['is_enabled' => true],
        };
        $node->update([
            'name' => $validated['name'],
            'config_json' => $config,
            'next_node_key' => $node->type === 'condition' || $node->type === 'end' ? null : ($validated['next_node_key'] ?? null),
            'yes_node_key' => $node->type === 'condition' ? ($validated['yes_node_key'] ?? null) : null,
            'no_node_key' => $node->type === 'condition' ? ($validated['no_node_key'] ?? null) : null,
        ]);

        return back()->with('success', 'L’étape d’automatisation a été mise à jour.');
    }

    public function destroy(OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationVersion $version, OfferJourneyAutomationNode $node): RedirectResponse
    {
        $this->authorizeNode($journey, $automation, $version, $node);
        abort_if($version->nodes()->count() <= 1, 422, 'La séquence doit conserver au moins une étape.');
        $version->nodes()->where('next_node_key', $node->node_key)->update(['next_node_key' => $node->next_node_key]);
        $version->nodes()->where('yes_node_key', $node->node_key)->update(['yes_node_key' => $node->next_node_key]);
        $version->nodes()->where('no_node_key', $node->node_key)->update(['no_node_key' => $node->next_node_key]);
        $node->delete();

        return back()->with('success', 'L’étape a été supprimée du brouillon.');
    }

    private function authorizeVersion(OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationVersion $version): void
    {
        $this->authorize('update', $journey);
        abort_unless((int) $automation->offer_journey_id === (int) $journey->id && (int) $version->offer_journey_automation_id === (int) $automation->id, 404);
        abort_unless($version->status === 'draft', 409, 'Créez une nouvelle version avant de modifier cette séquence.');
    }

    private function authorizeNode(OfferJourney $journey, OfferJourneyAutomation $automation, OfferJourneyAutomationVersion $version, OfferJourneyAutomationNode $node): void
    {
        $this->authorizeVersion($journey, $automation, $version);
        abort_unless((int) $node->offer_journey_automation_version_id === (int) $version->id, 404);
    }

    private function label(string $type): string
    {
        return ['wait' => 'Attendre', 'condition' => 'Vérifier une condition', 'action' => 'Mettre à jour le suivi', 'email' => 'Envoyer un message', 'end' => 'Fin de la séquence'][$type];
    }

    private function defaults(string $type): array
    {
        return match ($type) {
            'wait' => ['delay_minutes' => 1440, 'relative_delay' => true, 'is_enabled' => true],
            'condition' => ['condition_type' => 'marketing_consent', 'value' => null, 'is_enabled' => true],
            'action' => ['action_type' => 'create_task', 'value' => 'Recontacter ce contact', 'is_enabled' => true],
            'email' => ['delay_minutes' => 0, 'category' => 'marketing', 'subject' => '', 'body' => '', 'is_enabled' => true],
            default => ['is_enabled' => true],
        };
    }
}
