<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use Illuminate\Database\Eloquent\Builder;

class OfferJourneySegmentQuery
{
    public function apply(Builder $query, OfferJourneySegment $segment): Builder
    {
        $rules = $segment->relationLoaded('rules') ? $segment->rules : $segment->rules()->get();

        if ($segment->match_type === 'any') {
            $query->where(function (Builder $group) use ($rules) {
                foreach ($rules as $rule) {
                    $group->orWhere(fn (Builder $inner) => $this->applyRule($inner, $rule));
                }
            });
        } else {
            foreach ($rules as $rule) {
                $query->where(fn (Builder $inner) => $this->applyRule($inner, $rule));
            }
        }

        return $query;
    }

    private function applyRule(Builder $inner, $rule): void
    {
        $value = $rule->value_json['value'] ?? null;
        match ($rule->field) {
            'status' => $inner->where('status', $rule->operator === 'not_equals' ? '!=' : '=', (string) $value),
            'tag' => $this->applyTagRule($inner, (int) $value, $rule->operator),
            'journey' => $inner->whereHas('entries', fn ($entries) => $entries->where('offer_journey_id', (int) $value)),
            'inactive_days' => $inner->where('last_activity_at', '<=', now()->subDays(max(1, (int) $value))),
            'marketing_consent' => $inner->whereHas('consents', fn ($consents) => $consents
                ->where('purpose', 'marketing_follow_up')->where('status', 'granted')->whereNull('withdrawn_at')),
            default => $inner->whereRaw('1 = 0'),
        };
    }

    private function applyTagRule(Builder $inner, int $tagId, string $operator): void
    {
        if ($operator === 'missing') {
            $inner
                ->whereDoesntHave('tags', fn (Builder $tags) => $tags->whereKey($tagId))
                ->whereDoesntHave('clientProfile.marketingTags', fn (Builder $tags) => $tags->whereKey($tagId));

            return;
        }

        $inner->where(function (Builder $tags) use ($tagId) {
            $tags
                ->whereHas('tags', fn (Builder $query) => $query->whereKey($tagId))
                ->orWhereHas('clientProfile.marketingTags', fn (Builder $query) => $query->whereKey($tagId));
        });
    }
}
