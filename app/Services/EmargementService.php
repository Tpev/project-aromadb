<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Emargement;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmargementService
{
    /**
     * Create an emargement for an appointment (14-day expiry by default).
     */
    public function createForAppointment(Appointment $appointment, int $days = 14): Emargement
    {
        // Rotate any previous pending token if you want uniqueness per appt,
        // or simply allow multiple records. Here we just create a new one.
        $token = Str::random(64);

        return Emargement::create([
            'appointment_id' => $appointment->id,
            'therapist_id'   => $appointment->user_id,
            'client_email'   => optional($appointment->clientProfile)->email,
            'token'          => $token,
            'expires_at'     => Carbon::now()->addDays($days),
            'status'         => 'pending',
            'meta'           => [
                'client' => [
                    'id'    => optional($appointment->clientProfile)->id,
                    'first' => optional($appointment->clientProfile)->first_name,
                    'last'  => optional($appointment->clientProfile)->last_name,
                    'email' => optional($appointment->clientProfile)->email,
                ],
                'product' => [
                    'id'       => optional($appointment->product)->id,
                    'name'     => optional($appointment->product)->name,
                    'duration' => optional($appointment->product)->duration,
                ],
                'therapist' => [
                    'id'   => optional($appointment->user)->id,
                    'name' => optional($appointment->user)->name,
                ],
                'appointment' => [
                    'id'   => $appointment->id,
                    'date' => optional($appointment->appointment_date)?->toIso8601String(),
                ],
            ],
        ]);
    }

    /**
     * Rotate token and extend expiry (used for resend).
     */
    public function rotateToken(Emargement $em, int $days = 14): Emargement
    {
        $em->update([
            'token'      => Str::random(64),
            'expires_at' => Carbon::now()->addDays($days),
            'status'     => 'pending',
        ]);

        return $em->refresh();
    }

    /**
     * Mark an emargement as signed, capture IP + User-Agent, store signature image path if provided,
     * regenerate evidence PDF and persist the pdf_path.
     */
    public function markSigned(Emargement $em, ?string $signaturePath, string $ip, ?string $userAgent = null): Emargement
    {
        $em->update([
            'status'               => 'signed',
            'signed_at'            => Carbon::now(),
            'signer_ip'            => $ip,
            'signature_image_path' => $signaturePath,
            'signer_user_agent'    => $userAgent ? mb_substr($userAgent, 0, 512) : null,
        ]);

        // Generate & save PDF, store path
        $pdfPath = $this->generateEvidencePdf($em);
        $em->update(['pdf_path' => $pdfPath]);

        return $em->refresh();
    }

    /**
     * Render and store the proof PDF. Returns the relative path under "public" disk.
     */
    public function generateEvidencePdf(Emargement $em): string
    {
        // Ensure FR locale
        App::setLocale('fr');
        Carbon::setLocale('fr');

        // Build all data for the PDF view
        $meta        = is_array($em->meta) ? $em->meta : (json_decode($em->meta ?? '[]', true) ?: []);
        $appointment = $em->appointment;
        $client      = $appointment?->clientProfile;
        $product     = $appointment?->product;
        $therapist   = $appointment?->user;

        $signedAt  = $em->signed_at ? Carbon::parse($em->signed_at) : null;
        $apptAt    = $appointment?->appointment_date ? Carbon::parse($appointment->appointment_date) : null;

        $tokenTail = substr($em->token, -8);
        $hashTail  = substr(hash('sha256', $em->token.$em->id.($signedAt?->timestamp ?? '')), 0, 16);

        // Signature image as base64 (if exists)
        $signatureBase64 = null;
        if ($em->signature_image_path && Storage::disk('public')->exists($em->signature_image_path)) {
            $bin = Storage::disk('public')->get($em->signature_image_path);
            $signatureBase64 = 'data:image/png;base64,'.base64_encode($bin);
        }

        $viewData = [
            'em'           => $em,
            'meta'         => $meta,
            'client'       => $client,
            'product'      => $product,
            'therapist'    => $therapist,
            'appointment'  => $appointment,
            'signedAt'     => $signedAt,
            'apptAt'       => $apptAt,
            'tokenTail'    => $tokenTail,
            'hashTail'     => $hashTail,
            'signatureB64' => $signatureBase64,
        ];

        // Render the PDF using whichever view exists (singular first for back-compat)
        $html = view()->first(
            ['emargement.pdf', 'emargements.pdf'],
            $viewData
        )->render();

        // Use Dompdf via the global PDF facade (barryvdh/laravel-dompdf)
        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html)->setPaper('A4', 'portrait');

        // Ensure directory exists on public disk
        if (!Storage::disk('public')->exists('emargements')) {
            Storage::disk('public')->makeDirectory('emargements');
        }

        $relativePath = "emargements/{$em->id}-evidence.pdf";
        Storage::disk('public')->put($relativePath, $pdf->output());

        return $relativePath;
    }
}
