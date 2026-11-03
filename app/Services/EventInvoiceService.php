<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EventInvoiceService
{
    public function reservation(int $id, int $userId, bool $lock = false): Reservation
    {
        return Reservation::with(['event.associatedProduct', 'invoice'])
            ->whereHas('event', fn ($query) => $query->where('user_id', $userId))
            ->when($lock, fn ($query) => $query->lockForUpdate())->findOrFail($id);
    }

    public function ensureEligible(Reservation $reservation): void
    {
        if (! $reservation->isActive()) {
            throw ValidationException::withMessages(['event_reservation_id' => 'Cette participation est annulée ou inactive.']);
        }
        if ($reservation->status === 'pending_payment' || ($reservation->stripe_session_id && $reservation->status !== 'paid')) {
            throw ValidationException::withMessages(['event_reservation_id' => 'Attendez la confirmation du paiement en ligne avant de facturer cette participation.']);
        }
        if ($reservation->status === 'paid' && ((float) $reservation->amount_ttc <= 0 || strtolower($reservation->currency ?? 'eur') !== 'eur')) {
            throw ValidationException::withMessages(['event_reservation_id' => 'Vérifiez le montant et la devise du paiement de cette participation avant de créer sa facture.']);
        }
    }

    public function matchingClients(Reservation $reservation)
    {
        $email = mb_strtolower(trim((string) $reservation->email));

        return ClientProfile::where('user_id', $reservation->event->user_id)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$email])->whereRaw('? <> ?', [$email, ''])->get();
    }

    public function validateRecipient(Reservation $reservation, array $data): void
    {
        $client = ClientProfile::where('user_id', $reservation->event->user_id)->find($data['client_profile_id'] ?? null);
        $email = mb_strtolower(trim((string) $reservation->email));
        if (! $client || ! empty($data['corporate_client_id']) || ! empty($data['appointment_id'])
            || ($email !== '' && mb_strtolower(trim((string) $client->email)) !== $email)) {
            throw ValidationException::withMessages(['client_profile_id' => 'Choisissez le dossier client correspondant au participant de cet événement.']);
        }
    }

    public function snapshot(Reservation $reservation): array
    {
        $event = $reservation->event;

        return [
            'event_id' => $event->id,
            'reservation_id' => $reservation->id,
            'name' => $event->name,
            'period' => $event->formatted_period,
            'start_date_time' => $event->start_date_time?->toIso8601String(),
            'end_date_time' => $event->ends_at?->toIso8601String(),
            'location' => $event->location,
            'participant_name' => $reservation->full_name,
            'participant_email' => $reservation->email,
        ];
    }

    public function item(Reservation $reservation): array
    {
        $event = $reservation->event;
        $product = $event->associatedProduct?->user_id === $event->user_id ? $event->associatedProduct : null;
        $tax = (float) ($event->tax_rate ?? $product?->tax_rate ?? 0);
        $price = $reservation->status === 'paid'
            ? round((float) $reservation->amount_ttc / (1 + $tax / 100), 2)
            : (float) ($event->price ?? $product?->price ?? 0);
        $multiDay = $event->ends_at && ! $event->start_date_time->isSameDay($event->ends_at);

        return [
            'type' => 'custom', 'label' => Str::limit('Participation — '.$event->name, 255, ''),
            'description' => implode("\n", array_filter([$event->formatted_period, $event->location])),
            'quantity' => 1, 'unit_price' => $price, 'tax_rate' => $tax,
            'service_date' => $multiDay ? null : $event->start_date_time?->toDateString(),
            'service_period_start' => $multiDay ? $event->start_date_time->toDateString() : null,
            'service_period_end' => $multiDay ? $event->ends_at->toDateString() : null,
        ];
    }

    public function recordPayment(Invoice $invoice, Reservation $reservation, ?string $paymentDate): void
    {
        if ($reservation->status !== 'paid') {
            return;
        }
        if ((int) round($invoice->total_amount_with_tax * 100) !== (int) round($reservation->amount_ttc * 100)) {
            throw ValidationException::withMessages(['items' => 'Le total TTC de la facture doit correspondre au montant déjà payé pour cette participation. Vérifiez le prix, la TVA et les remises.']);
        }
        $date = $reservation->payment_confirmation_requested_at?->toDateString() ?? $paymentDate;
        if (! $date) {
            throw ValidationException::withMessages(['event_payment_date' => 'Renseignez la date du paiement déjà reçu pour cette participation.']);
        }
        $reference = $reservation->stripe_payment_intent_id ?: 'event-reservation-'.$reservation->id;
        $provider = $reservation->stripe_payment_intent_id ? 'stripe' : 'event_reservation';
        $existing = Receipt::where('user_id', $invoice->user_id)->where('provider', $provider)
            ->where('provider_reference', $reference)->first();
        if ($existing && (int) $existing->invoice_id !== (int) $invoice->id) {
            throw ValidationException::withMessages(['event_reservation_id' => 'Ce paiement est déjà enregistré sur une autre facture. Consultez cette facture avant de poursuivre.']);
        }

        app(ReceiptRecordingService::class)->recordInvoicePayment(
            $invoice, (float) $reservation->amount_ttc, $date,
            paymentMethod: 'other', source: 'payment',
            note: 'Paiement en ligne déjà reçu pour la participation à '.$reservation->event->name.'.',
            provider: $provider, providerReference: $reference, actor: auth()->user(),
        );
    }
}
