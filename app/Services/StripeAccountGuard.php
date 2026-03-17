<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeAccountGuard
{
    public function canAcceptOnlineCheckout(User $user): bool
    {
        return $this->status($user)['ready'];
    }

    /**
     * @return array{ready: bool, reason: string}
     */
    public function status(User $user): array
    {
        if (! $user->stripe_account_id) {
            return ['ready' => false, 'reason' => 'missing_account'];
        }

        $cacheKey = 'stripe-account-ready:' . $user->id . ':' . $user->stripe_account_id;

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user) {
            try {
                $stripe = new StripeClient((string) config('services.stripe.secret'));
                $account = $stripe->accounts->retrieve($user->stripe_account_id, []);

                $isReady = (bool) ($account->details_submitted ?? false)
                    && (bool) ($account->charges_enabled ?? false);

                return [
                    'ready' => $isReady,
                    'reason' => $isReady ? 'ok' : 'incomplete_onboarding',
                ];
            } catch (\Throwable $e) {
                Log::warning('Stripe account readiness check failed', [
                    'user_id' => $user->id,
                    'stripe_account_id' => $user->stripe_account_id,
                    'error' => $e->getMessage(),
                ]);

                return ['ready' => false, 'reason' => 'stripe_error'];
            }
        });
    }
}

