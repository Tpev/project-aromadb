<?php

namespace App\Services;

use App\Mail\QuestionnaireSentMail;
use App\Models\Appointment;
use App\Models\Product;
use App\Models\Questionnaire;
use App\Models\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AppointmentQuestionnaireAutomationService
{
    public const SOURCE = 'booking_automation';

    public function dispatchForConfirmedAppointment(Appointment $appointment): ?Response
    {
        $appointment->loadMissing(['product.bookingQuestionnaire', 'clientProfile', 'user']);

        $product = $appointment->product;
        $clientProfile = $appointment->clientProfile;
        $therapist = $appointment->user;

        if (!$product || !$clientProfile || !$therapist) {
            return null;
        }

        if (!$product->booking_questionnaire_enabled || !$product->booking_questionnaire_id) {
            return null;
        }

        if ($appointment->isCancelled()) {
            return null;
        }

        if (empty($clientProfile->email)) {
            Log::info('Skipping automated questionnaire send because client has no email.', [
                'appointment_id' => $appointment->id,
                'client_profile_id' => $clientProfile->id,
                'product_id' => $product->id,
            ]);
            return null;
        }

        $questionnaire = Questionnaire::query()
            ->whereKey($product->booking_questionnaire_id)
            ->where('user_id', $therapist->id)
            ->first();

        if (!$questionnaire) {
            Log::warning('Skipping automated questionnaire send because questionnaire is missing or not owned by therapist.', [
                'appointment_id' => $appointment->id,
                'product_id' => $product->id,
                'questionnaire_id' => $product->booking_questionnaire_id,
                'therapist_id' => $therapist->id,
            ]);
            return null;
        }

        $existingResponse = Response::query()
            ->where('appointment_id', $appointment->id)
            ->where('questionnaire_id', $questionnaire->id)
            ->where('source', self::SOURCE)
            ->first();

        if ($existingResponse) {
            return $existingResponse;
        }

        if (
            $product->usesFirstTimeQuestionnaireAutomation()
            && $this->hasAnotherNonCancelledBookingForThisProduct($appointment)
        ) {
            return null;
        }

        $response = Response::create([
            'questionnaire_id' => $questionnaire->id,
            'client_profile_id' => $clientProfile->id,
            'appointment_id' => $appointment->id,
            'token' => Str::random(32),
            'answers' => [],
            'is_completed' => false,
            'source' => self::SOURCE,
        ]);

        try {
            Mail::to($clientProfile->email)->queue(
                new QuestionnaireSentMail(
                    $therapist->name,
                    $questionnaire->title,
                    route('questionnaires.fill', ['token' => $response->token]),
                    (string) ($clientProfile->first_name ?: 'Client')
                )
            );
        } catch (\Throwable $e) {
            Log::error('Automated questionnaire email dispatch failed.', [
                'appointment_id' => $appointment->id,
                'response_id' => $response->id,
                'questionnaire_id' => $questionnaire->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $response;
    }

    private function hasAnotherNonCancelledBookingForThisProduct(Appointment $appointment): bool
    {
        return Appointment::query()
            ->where('user_id', $appointment->user_id)
            ->where('client_profile_id', $appointment->client_profile_id)
            ->where('product_id', $appointment->product_id)
            ->whereKeyNot($appointment->id)
            ->notCancelled()
            ->exists();
    }
}
