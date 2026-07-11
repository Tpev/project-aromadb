<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyAutomationVersion;

class OfferJourneyAutomationSimulator
{
    public function simulate(OfferJourneyAutomationVersion $version, array $context): array
    {
        $nodes = $version->nodes->keyBy('node_key');
        $current = $version->entry_node_key ?: $version->nodes->sortBy('position_y')->first()?->node_key;
        $result = [];
        $visited = [];

        while ($current && count($result) < 20 && ! isset($visited[$current])) {
            $visited[$current] = true;
            $node = $nodes->get($current);
            if (! $node) {
                $result[] = ['name' => 'Étape introuvable', 'type' => 'error', 'detail' => $current];
                break;
            }

            $config = $node->config_json ?? [];
            $result[] = [
                'name' => $node->name,
                'type' => $node->type,
                'detail' => match ($node->type) {
                    'email' => ($config['is_enabled'] ?? false) ? 'Message prévu, sans envoi pendant ce test' : 'Message désactivé',
                    'wait' => 'Délai prévu : '.((int) ($config['delay_minutes'] ?? 0)).' minute(s)',
                    'condition' => 'Condition vérifiée sans modifier les données',
                    'action' => 'Action prévue, sans modification pendant ce test',
                    'end' => 'Fin du parcours',
                    default => 'Étape testée',
                },
            ];

            $current = $node->type === 'condition'
                ? ($this->matches($config, $context) ? $node->yes_node_key : $node->no_node_key)
                : $node->next_node_key;
        }

        return $result;
    }

    private function matches(array $config, array $context): bool
    {
        return match ($config['condition_type'] ?? 'marketing_consent') {
            'marketing_consent' => (bool) ($context['marketing_consent'] ?? false),
            'converted' => (bool) ($context['converted'] ?? false),
            'has_tag' => in_array((string) ($config['value'] ?? ''), array_map('strval', $context['tags'] ?? []), true),
            'inactive_days' => (int) ($context['inactive_days'] ?? 0) >= (int) ($config['value'] ?? 0),
            default => false,
        };
    }
}
