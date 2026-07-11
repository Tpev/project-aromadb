<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneySupportAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OfferJourneySupportAuditLogger
{
    public function record(
        ?User $actor,
        string $action,
        ?Model $target,
        string $reason,
        ?array $before = null,
        ?array $after = null,
        ?Request $request = null
    ): OfferJourneySupportAudit {
        $ip = $request?->ip();

        return OfferJourneySupportAudit::query()->create([
            'actor_user_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target?->getMorphClass(),
            'target_id' => $target?->getKey(),
            'reason' => Str::limit($reason, 2000),
            'before_json' => $before,
            'after_json' => $after,
            'request_ip_hash' => $ip ? hash_hmac('sha256', $ip, (string) config('app.key')) : null,
            'request_id' => $request?->header('X-Request-ID'),
            'occurred_at' => now(),
        ]);
    }
}
