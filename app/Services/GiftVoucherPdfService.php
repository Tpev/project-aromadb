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
        $qrBuild = $this->buildQrImageSrc($portalUrl);

        $backgroundBuild = $this->buildPdfSafeBackgroundSource($voucher->background_path_snapshot);

        Log::info('Gift voucher PDF render starting.', [
            'voucher_id' => $voucher->id,
            'voucher_code' => $voucher->code,
            'therapist_id' => $therapist?->id,
            'therapist_slug' => $therapist?->slug,
            'portal_url' => $portalUrl,
            'qr_mode' => $qrBuild['mode'],
            'qr_available' => $qrBuild['src'] !== null,
            'background_mode_snapshot' => $voucher->background_mode_snapshot,
            'background_path_snapshot' => $voucher->background_path_snapshot,
            'background_exists' => $voucher->background_path_snapshot
                ? Storage::disk('public')->exists($voucher->background_path_snapshot)
                : false,
            'background_source_type' => $backgroundBuild['type'],
            'background_available' => $backgroundBuild['src'] !== null,
        ]);

        $pdf = Pdf::loadView('pdf.gift-voucher', [
            'voucher' => $voucher,
            'therapist' => $therapist,
            'portalUrl' => $portalUrl,
            'qrImageSrc' => $qrBuild['src'],
            'backgroundImageSrc' => $backgroundBuild['src'],
        ])->setPaper('a4', 'portrait');

        $output = $pdf->output();

        Log::info('Gift voucher PDF render completed.', [
            'voucher_id' => $voucher->id,
            'voucher_code' => $voucher->code,
            'pdf_bytes' => strlen($output),
            'qr_mode' => $qrBuild['mode'],
            'background_source_type' => $backgroundBuild['type'],
        ]);

        return $output;
    }

    public function resolvePortalUrl(GiftVoucher $voucher): string
    {
        $therapist = $voucher->therapist;

        if ($therapist?->slug) {
            return route('therapist.show', ['slug' => $therapist->slug]);
        }

        return route('appointments.createPatient', ['therapist' => $therapist?->id]);
    }

    public function buildQrImageSrc(string $portalUrl): array
    {
        try {
            $qrPng = QrCode::format('png')
                ->size(240)
                ->margin(1)
                ->generate($portalUrl);

            return [
                'src' => 'data:image/png;base64,' . base64_encode($qrPng),
                'mode' => 'png',
            ];
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

            return [
                'src' => 'data:image/svg+xml;base64,' . base64_encode($qrSvg),
                'mode' => 'svg',
            ];
        } catch (\Throwable $e) {
            Log::warning('Gift voucher QR code could not be generated.', [
                'portal_url' => $portalUrl,
                'error' => $e->getMessage(),
            ]);

            return [
                'src' => null,
                'mode' => 'none',
            ];
        }
    }

    private function buildPdfSafeBackgroundSource(?string $backgroundPath): array
    {
        if (! $backgroundPath || ! Storage::disk('public')->exists($backgroundPath)) {
            return [
                'src' => null,
                'type' => 'none',
            ];
        }

        try {
            $binary = Storage::disk('public')->get($backgroundPath);
            $mime = (string) (Storage::disk('public')->mimeType($backgroundPath) ?: '');
            $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
            $manager = new ImageManager($driver);
            $jpeg = $manager
                ->read($binary)
                ->orient()
                ->cover(self::PDF_BACKGROUND_WIDTH, self::PDF_BACKGROUND_HEIGHT)
                ->encode(new JpegEncoder(quality: 82));

            Log::info('Gift voucher background prepared for PDF.', [
                'background_path' => $backgroundPath,
                'original_mime' => $mime,
                'original_bytes' => strlen($binary),
                'prepared_bytes' => strlen((string) $jpeg),
                'width' => self::PDF_BACKGROUND_WIDTH,
                'height' => self::PDF_BACKGROUND_HEIGHT,
            ]);

            return [
                'src' => 'data:image/jpeg;base64,' . base64_encode((string) $jpeg),
                'type' => 'data-jpeg',
            ];
        } catch (\Throwable $e) {
            Log::warning('Gift voucher background could not be rendered for PDF.', [
                'background_path' => $backgroundPath,
                'error' => $e->getMessage(),
            ]);

            return [
                'src' => null,
                'type' => 'failed',
            ];
        }
    }
}
