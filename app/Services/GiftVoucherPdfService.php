<?php

namespace App\Services;

use App\Models\GiftVoucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class GiftVoucherPdfService
{
    private const PDF_BACKGROUND_WIDTH = 794;
    private const PDF_BACKGROUND_HEIGHT = 1123;

    public function renderPdf(GiftVoucher $voucher): string
    {
        $therapist = $voucher->therapist;

        $portalUrl = $this->resolvePortalUrl($voucher);
        $qrImageSrc = $this->buildQrImageSrc($portalUrl);

        $backgroundImageSrc = $this->buildPdfSafeBackgroundSource($voucher->background_path_snapshot);

        $pdf = Pdf::loadView('pdf.gift-voucher', [
            'voucher' => $voucher,
            'therapist' => $therapist,
            'portalUrl' => $portalUrl,
            'qrImageSrc' => $qrImageSrc,
            'backgroundImageSrc' => $backgroundImageSrc,
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

    public function buildQrImageSrc(string $portalUrl): ?string
    {
        try {
            $qrPng = QrCode::format('png')
                ->size(240)
                ->margin(1)
                ->generate($portalUrl);

            return 'data:image/png;base64,' . base64_encode($qrPng);
        } catch (\Throwable $e) {
            Log::info('Gift voucher PNG QR generation failed, trying SVG fallback.', [
                'portal_url' => $portalUrl,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            $qrSvg = QrCode::format('svg')
                ->size(240)
                ->margin(1)
                ->generate($portalUrl);

            return 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        } catch (\Throwable $e) {
            Log::warning('Gift voucher QR code could not be generated.', [
                'portal_url' => $portalUrl,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function buildPdfSafeBackgroundSource(?string $backgroundPath): ?string
    {
        if (! $backgroundPath || ! Storage::disk('public')->exists($backgroundPath)) {
            return null;
        }

        try {
            $binary = Storage::disk('public')->get($backgroundPath);
            $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $manager = new ImageManager($driver);
            $jpeg = $manager
                ->read($binary)
                ->orient()
                ->cover(self::PDF_BACKGROUND_WIDTH, self::PDF_BACKGROUND_HEIGHT)
                ->encode(new JpegEncoder(quality: 82));

            return 'data:image/jpeg;base64,' . base64_encode((string) $jpeg);
        } catch (\Throwable $e) {
            Log::warning('Gift voucher background could not be rendered for PDF.', [
                'background_path' => $backgroundPath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
