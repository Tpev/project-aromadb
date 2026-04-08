<?php

namespace App\Services;

use App\Models\GiftVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\ImageManager;

class GiftVoucherPdfService
{
    public function renderPdf(GiftVoucher $voucher): string
    {
        $therapist = $voucher->therapist;

        $portalUrl = $this->resolvePortalUrl($voucher);
        $qrSvg = $this->buildQrSvgMarkup($portalUrl);

        $backgroundBase64 = $this->buildPdfSafeBackgroundDataUri($voucher->background_path_snapshot);

        $pdf = Pdf::loadView('pdf.gift-voucher', [
            'voucher' => $voucher,
            'therapist' => $therapist,
            'portalUrl' => $portalUrl,
            'qrSvg' => $qrSvg,
            'backgroundBase64' => $backgroundBase64,
        ])->setPaper('a4', 'portrait');

        return $pdf->output();
    }

    public function resolvePortalUrl(GiftVoucher $voucher): string
    {
        $therapist = $voucher->therapist;

        if ($therapist?->slug) {
            return route('therapist.show', ['slug' => $therapist->slug]);
        }

        return route('appointments.createPatient', ['therapist' => $therapist?->id]);
    }

    public function buildQrSvgMarkup(string $portalUrl): ?string
    {
        try {
            return QrCode::format('svg')
                ->size(240)
                ->margin(1)
                ->generate($portalUrl);
        } catch (\Throwable $e) {
            Log::warning('Gift voucher QR code could not be generated.', [
                'portal_url' => $portalUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildPdfSafeBackgroundDataUri(?string $backgroundPath): ?string
    {
        if (! $backgroundPath || ! Storage::disk('public')->exists($backgroundPath)) {
            return null;
        }

        try {
            $binary = Storage::disk('public')->get($backgroundPath);
            $mime = (string) (Storage::disk('public')->mimeType($backgroundPath) ?: '');

            // Keep native format when already PDF-safe.
            if (in_array($mime, ['image/jpeg', 'image/jpg', 'image/png'], true)) {
                return 'data:' . $mime . ';base64,' . base64_encode($binary);
            }

            // Convert (ex: WEBP) to PNG for DomPDF compatibility.
            $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $manager = new ImageManager($driver);
            $png = $manager
                ->read($binary)
                ->orient()
                ->cover(1240, 1754)
                ->encode(new PngEncoder());

            return 'data:image/png;base64,' . base64_encode((string) $png);
        } catch (\Throwable $e) {
            Log::warning('Gift voucher background could not be rendered for PDF.', [
                'background_path' => $backgroundPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
