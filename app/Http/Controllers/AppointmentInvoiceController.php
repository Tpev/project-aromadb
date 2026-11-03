<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Services\InvoiceActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppointmentInvoiceController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function associate(
        Request $request,
        Appointment $appointment,
        InvoiceActivityService $activity
    ) {
        $this->authorize('update', $appointment);

        $validated = $request->validate([
            'invoice_id' => ['required', 'integer'],
            'confirm_reassign' => ['nullable', 'boolean'],
        ]);

        return DB::transaction(function () use ($request, $appointment, $validated, $activity) {
            $appointment = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $invoice = Invoice::query()
                ->where('user_id', $request->user()->id)
                ->where('type', 'invoice')
                ->lockForUpdate()
                ->findOrFail($validated['invoice_id']);

            if ((int) $invoice->client_profile_id !== (int) $appointment->client_profile_id) {
                return back()->with('error', 'Cette facture ne correspond pas au client du rendez-vous.');
            }

            if ($invoice->appointment_id && (int) $invoice->appointment_id !== (int) $appointment->id
                && ! $request->boolean('confirm_reassign')) {
                return back()->with('error', 'Cette facture est déjà associée à un autre rendez-vous. Confirmez la réaffectation pour continuer.');
            }

            $previousAppointmentId = $invoice->appointment_id;
            if ($previousAppointmentId && (int) $previousAppointmentId !== (int) $appointment->id
                && $invoice->receipts()->where('provider', 'gift_voucher')->exists()) {
                return back()->with('error', 'Cette facture contient un règlement par bon cadeau lié à son rendez-vous. Elle ne peut pas être réaffectée.');
            }
            $invoice->forceFill(['appointment_id' => $appointment->id])->saveQuietly();

            // Preserve legacy payments: an existing manual receipt may already represent
            // this voucher. Only carry usage automatically onto an unpaid, empty ledger.
            if ($invoice->receipts()->doesntExist()
                && ! in_array($invoice->normalizedStatus(), ['payee', 'paid', 'partiellement payee', 'partially paid'], true)) {
                app(\App\Services\AppointmentGiftVoucherService::class)->recordOnInvoice($appointment, $invoice);
            }

            $activity->record(
                $invoice,
                'appointment_associated',
                'Facture associée au rendez-vous du '.$appointment->appointment_date->format('d/m/Y').'.',
                $request->user(),
                ['previous_appointment_id' => $previousAppointmentId, 'appointment_id' => $appointment->id]
            );

            return back()->with('success', 'Facture associée au rendez-vous.');
        });
    }
}
