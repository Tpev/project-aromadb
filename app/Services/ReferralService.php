<?php

namespace App\Services;

use App\Models\ReferralCode;
use App\Models\ReferralInvite;
use App\Models\User;
use Illuminate\Support\Str;

class ReferralService
{
    public function getOrCreateCodeFor(User $user): ReferralCode
    {
        return ReferralCode::firstOrCreate(
            ['user_id' => $user->id],
            ['code' => $this->generateReadableCode($user)]
        );
    }

    public function createInvite(User $referrer, string $email, ?string $message = null): ReferralInvite
    {
        // Token unique et non devinable
        $token = Str::random(48);

        return ReferralInvite::create([
            'referrer_user_id' => $referrer->id,
            'email' => mb_strtolower(trim($email)),
            'token' => $token,
            'status' => 'sent',
            'message' => $message,
            'expires_at' => now()->addDays(30),
        ]);
    }

    /**
     * Attribue un parrainage à un nouvel utilisateur.
     * - via invite token (prioritaire)
     * - sinon via code "ref"
     */
    public function attributeNewUser(User $newUser, ?string $refCode, ?string $inviteToken): void
    {
        if ($newUser->referred_by_user_id) {
            return;
        }

        // 1) Invitation token prioritaire
        if ($inviteToken) {
            $invite = ReferralInvite::where('token', $inviteToken)->first();
            if ($invite && !$invite->isExpired()) {
                $referrer = $invite->referrer;

                // On attribue uniquement si le parrain est thérapeute
                if ($referrer && $referrer->is_therapist) {
                    $invite->update([
                        'status' => 'signed_up',
                        'invited_user_id' => $newUser->id,
                        'signed_up_at' => now(),
                    ]);

                    $newUser->forceFill([
                        'referred_by_user_id' => $referrer->id,
                        'referral_invite_id' => $invite->id,
                        'referral_code_used' => $refCode,
                        'referral_attributed_at' => now(),
                    ])->save();

                    return;
                }
            }
        }

        // 2) Sinon par code ref
        if ($refCode) {
            $code = ReferralCode::where('code', $refCode)->first();
            if ($code && $code->user && $code->user->is_therapist) {
                // Empêche de s’auto-parrainer
                if ($code->user_id === $newUser->id) {
                    return;
                }

                $newUser->forceFill([
                    'referred_by_user_id' => $code->user_id,
                    'referral_code_used' => $refCode,
                    'referral_attributed_at' => now(),
                ])->save();
            }
        }
    }

    /**
     * Quand un utilisateur devient payant (à appeler depuis ton workflow licence/stripe).
     * Aucune récompense automatique : on marque juste "converted".
     */
    public function markConverted(User $paidUser): void
    {
        if (!$paidUser->referred_by_user_id || $paidUser->referral_converted_at) {
            return;
        }

        $paidUser->forceFill([
            'referral_converted_at' => now(),
        ])->save();

        if ($paidUser->referral_invite_id) {
            ReferralInvite::where('id', $paidUser->referral_invite_id)->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);
        }
    }

    private function generateReadableCode(User $user): string
    {
        // Base lisible depuis name / email
        $base = $user->name
            ? Str::slug($user->name)
            : Str::before($user->email, '@');

        $base = Str::upper(Str::limit(preg_replace('/[^A-Z0-9]+/i', '-', $base), 16, ''));

        // Suffixe aléatoire court
        $suffix = Str::upper(Str::random(5));

        return trim($base, '-') . '-' . $suffix;
    }
}
