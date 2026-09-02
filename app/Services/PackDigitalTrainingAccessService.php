<?php

namespace App\Services;

use App\Mail\DigitalTrainingAccessMail;
use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\PackPurchase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class PackDigitalTrainingAccessService
{
    /**
     * @return array{trainings:int,created:int,emailed:int,email_failed:int,missing_email:bool}
     */
    public function grant(PackPurchase $purchase, bool $resend = false): array
    {
        $result = [
            'trainings' => 0,
            'created' => 0,
            'emailed' => 0,
            'email_failed' => 0,
            'missing_email' => false,
        ];

        $purchase->loadMissing(['pack.digitalTrainings', 'clientProfile']);

        if (
            ($purchase->purchase_type ?? 'pack') !== 'pack'
            || $purchase->status !== 'active'
            || ! $purchase->pack
        ) {
            return $result;
        }

        $client = $purchase->clientProfile;
        $participantName = trim(collect([$client?->first_name, $client?->last_name])->filter()->join(' '));
        $participantEmail = trim((string) $client?->email);
        $result['missing_email'] = $participantEmail === '';
        $reservedForSending = collect();

        DB::transaction(function () use (
            $purchase,
            $client,
            $participantName,
            $participantEmail,
            $resend,
            &$result,
            $reservedForSending
        ): void {
            $lockedPurchase = PackPurchase::query()
                ->with('pack.digitalTrainings')
                ->whereKey($purchase->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                ($lockedPurchase->purchase_type ?? 'pack') !== 'pack'
                || $lockedPurchase->status !== 'active'
                || ! $lockedPurchase->pack
            ) {
                return;
            }

            if ($lockedPurchase->digital_training_ids_snapshot === null) {
                $lockedPurchase->digital_training_ids_snapshot = $lockedPurchase->pack
                    ? $lockedPurchase->pack->digitalTrainings
                        ->where('user_id', $lockedPurchase->user_id)
                        ->pluck('id')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->all()
                    : [];
                $lockedPurchase->save();
            }

            $trainingIds = collect($lockedPurchase->digital_training_ids_snapshot)
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            $trainings = DigitalTraining::query()
                ->with('user')
                ->where('user_id', $lockedPurchase->user_id)
                ->whereIn('id', $trainingIds)
                ->get();

            $result['trainings'] = $trainings->count();

            foreach ($trainings as $training) {
                $enrollment = DigitalTrainingEnrollment::query()
                    ->where('pack_purchase_id', $purchase->id)
                    ->where('digital_training_id', $training->id)
                    ->lockForUpdate()
                    ->first();

                if (! $enrollment) {
                    $enrollment = new DigitalTrainingEnrollment([
                        'pack_purchase_id' => $purchase->id,
                        'digital_training_id' => $training->id,
                        'client_profile_id' => $client?->id,
                        'participant_name' => $participantName !== '' ? $participantName : null,
                        'participant_email' => $participantEmail !== '' ? $participantEmail : null,
                        'access_token' => (string) Str::uuid(),
                        'token_expires_at' => $this->accessExpiresAt($purchase),
                        'source' => DigitalTrainingEnrollment::SOURCE_PACK,
                    ]);
                    $result['created']++;
                } else {
                    $enrollment->fill([
                        'client_profile_id' => $client?->id,
                        'participant_name' => $participantName !== '' ? $participantName : $enrollment->participant_name,
                        'participant_email' => $participantEmail !== '' ? $participantEmail : $enrollment->participant_email,
                        'source' => DigitalTrainingEnrollment::SOURCE_PACK,
                    ]);

                    if ($enrollment->token_expires_at?->isPast()) {
                        $enrollment->access_token = (string) Str::uuid();
                        $enrollment->token_expires_at = $this->accessExpiresAt($purchase);
                        $enrollment->access_email_sent_at = null;
                    }
                }

                $shouldSend = $participantEmail !== ''
                    && ($resend || $enrollment->access_email_sent_at === null);

                if ($shouldSend) {
                    // Reserve the send while holding the purchase lock so repeated webhooks stay idempotent.
                    $enrollment->access_email_sent_at = now();
                }

                $enrollment->save();

                if ($shouldSend) {
                    $reservedForSending->push($enrollment->load('training.user'));
                }
            }
        });

        foreach ($reservedForSending as $enrollment) {
            try {
                Mail::to($participantEmail)->send(new DigitalTrainingAccessMail($enrollment));
                $result['emailed']++;
            } catch (Throwable $exception) {
                $result['email_failed']++;
                $enrollment->forceFill(['access_email_sent_at' => null])->save();

                Log::error('Unable to send pack digital training access email.', [
                    'pack_purchase_id' => $purchase->id,
                    'digital_training_enrollment_id' => $enrollment->id,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }

        return $result;
    }

    public function revoke(PackPurchase $purchase): int
    {
        $revokedAt = now()->subSecond();

        return DigitalTrainingEnrollment::query()
            ->where('pack_purchase_id', $purchase->id)
            ->where('source', DigitalTrainingEnrollment::SOURCE_PACK)
            ->where(function ($query) use ($revokedAt): void {
                $query->whereNull('token_expires_at')
                    ->orWhere('token_expires_at', '>', $revokedAt);
            })
            ->update(['token_expires_at' => $revokedAt]);
    }

    private function accessExpiresAt(PackPurchase $purchase): Carbon
    {
        $defaultExpiry = now()->addMonths(6);

        if ($purchase->expires_at && $purchase->expires_at->lt($defaultExpiry)) {
            return $purchase->expires_at->copy();
        }

        return $defaultExpiry;
    }
}
