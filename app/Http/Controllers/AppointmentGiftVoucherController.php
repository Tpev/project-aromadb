<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentGiftVoucherService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class AppointmentGiftVoucherController extends Controller
{
    use AuthorizesRequests;

    public function preview(Request $request, Appointment $appointment, AppointmentGiftVoucherService $service)
    {
        $this->authorize('update', $appointment);
        abort_unless($request->user()->canUseFeature('gift_vouchers'), 403);
        $data = $request->validate(['gift_voucher_code' => ['required', 'string', 'max:64']]);

        return back()->withInput()->with('appointment_voucher_preview', $service->quote($appointment, $data['gift_voucher_code']));
    }

    public function store(Request $request, Appointment $appointment, AppointmentGiftVoucherService $service)
    {
        $this->authorize('update', $appointment);
        abort_unless($request->user()->canUseFeature('gift_vouchers'), 403);
        $data = $request->validate([
            'gift_voucher_code' => ['required', 'string', 'max:64'],
            'amount_cents' => ['required', 'integer', 'min:1'],
            'total_cents' => ['required', 'integer', 'min:1'],
        ]);
        $service->apply($appointment, $data['gift_voucher_code'], (int) $data['amount_cents'], (int) $data['total_cents']);

        return back()->with('success', 'Le règlement par bon cadeau est enregistré. Le solde du bon a été déduit une seule fois.');
    }
}
