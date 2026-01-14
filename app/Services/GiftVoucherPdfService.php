<?php

namespace App\Services;

use App\Models\GiftVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GiftVoucherPdfService
{
    public function renderPdf(GiftVoucher $voucher): string
    {
        $therapist = $voucher->therapist;

        // QR: point to therapist public portal (as requested)
        // If you want QR to a voucher lookup later, change URL accordingly.
        $portalUrl = url('/pro/' . ($therapist->slug ?? $therapist->id));

        // Generate QR PNG binary then base64 for embedding in HTML
        $qrPng = QrCode::format('png')
            ->size(240)
            ->margin(1)
            ->generate($portalUrl);

        $qrBase64 = 'data:image/png;base64,' . base64_encode($qrPng);

        $pdf = Pdf::loadView('pdf.gift-voucher', [
            'voucher' => $voucher,
            'therapist' => $therapist,
            'portalUrl' => $portalUrl,
            'qrBase64' => $qrBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }
}
