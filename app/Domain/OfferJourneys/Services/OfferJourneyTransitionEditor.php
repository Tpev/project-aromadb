<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OfferJourneyTransitionEditor
{
    public function update(OfferJourney $journey, OfferJourneyPage $page, array $data): void
    {
        $primaryTarget = ($data['transition_action'] ?? 'none') === 'next_page'
            ? (int) ($data['transition_page_id'] ?? 0)
            : null;
        $fallbackTarget = ($data['transition_condition'] ?? 'always') !== 'always'
            ? (int) ($data['fallback_page_id'] ?? 0)
            : null;

        foreach (array_filter([$primaryTarget, $fallbackTarget]) as $targetId) {
            if ($targetId === (int) $page->id || ! $journey->pages()->whereKey($targetId)->exists()) {
                throw ValidationException::withMessages(['transition_page_id' => 'Choisissez une autre étape de ce parcours.']);
            }
            if ($this->wouldCreateCycle($journey, $page->id, $targetId)) {
                throw ValidationException::withMessages(['transition_page_id' => 'Ce lien créerait une boucle dans le parcours.']);
            }
        }

        DB::transaction(function () use ($journey, $page, $data, $primaryTarget, $fallbackTarget) {
            $journey->transitions()->where('from_page_id', $page->id)->delete();
            if (($data['transition_action'] ?? 'none') === 'none') {
                return;
            }

            $journey->transitions()->create([
                'from_page_id' => $page->id,
                'to_page_id' => $primaryTarget ?: null,
                'trigger' => 'primary_cta',
                'condition_json' => ['type' => $data['transition_condition'] ?? 'always'],
                'external_action' => ($data['transition_action'] ?? null) === 'source' ? 'source_action' : null,
                'priority' => 0,
                'is_fallback' => false,
                'is_active' => true,
            ]);

            if ($fallbackTarget) {
                $journey->transitions()->create([
                    'from_page_id' => $page->id,
                    'to_page_id' => $fallbackTarget,
                    'trigger' => 'primary_cta',
                    'condition_json' => null,
                    'priority' => 1,
                    'is_fallback' => true,
                    'is_active' => true,
                ]);
            }
        });
    }

    private function wouldCreateCycle(OfferJourney $journey, int $fromId, int $targetId): bool
    {
        $edges = $journey->transitions()
            ->whereNot('from_page_id', $fromId)
            ->whereNotNull('to_page_id')
            ->get(['from_page_id', 'to_page_id'])
            ->groupBy('from_page_id');
        $stack = [$targetId];
        $visited = [];

        while ($stack !== []) {
            $current = array_pop($stack);
            if ($current === $fromId) {
                return true;
            }
            if (isset($visited[$current])) {
                continue;
            }
            $visited[$current] = true;
            foreach ($edges->get($current, collect()) as $edge) {
                $stack[] = (int) $edge->to_page_id;
            }
        }

        return false;
    }
}
