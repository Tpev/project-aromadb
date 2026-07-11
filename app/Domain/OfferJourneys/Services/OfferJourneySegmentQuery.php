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
            'tag' => $rule->operator === 'missing'
                ? $inner->whereDoesntHave('tags', fn ($tags) => $tags->whereKey((int) $value))
                : $inner->whereHas('tags', fn ($tags) => $tags->whereKey((int) $value)),
            'journey' => $inner->whereHas('entries', fn ($entries) => $entries->where('offer_journey_id', (int) $value)),
            'inactive_days' => $inner->where('last_activity_at', '<=', now()->subDays(max(1, (int) $value))),
            'marketing_consent' => $inner->whereHas('consents', fn ($consents) => $consents
                ->where('purpose', 'marketing_follow_up')->where('status', 'granted')->whereNull('withdrawn_at')),
            default => $inner->whereRaw('1 = 0'),
        };
    }
}
