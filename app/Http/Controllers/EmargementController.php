<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Emargement;
use App\Services\EmargementService;
use App\Jobs\SendEmargementEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class EmargementController extends Controller
{
    public function __construct(private EmargementService $service) {}

    /**
     * Send initial emargement link for an appointment.
     */
    public function send(Request $request, Appointment $appointment)
    {
        // Ownership guard
        if ($appointment->user_id !== auth()->id()) {
            abort(403);
        }

        // Preconditions
        if (!$appointment->product?->requires_emargement) {
            return back()->with('error', 'Cette prestation ne requiert pas d’émargement.');
        }
        if (empty($appointment->clientProfile?->email)) {
            return back()->with('error', 'Email client manquant.');
        }

        // Create record + mark sent
        $em = $this->service->createForAppointment($appointment);
        $appointment->update(['emargement_sent' => true]);

        // Email out
        dispatch(new SendEmargementEmailJob($em));

        return back()->with('success', 'Feuille d’émargement envoyée.');
    }

    /**
     * Resend (rotate token) for an existing emargement.
     */
    public function resend(Emargement $emargement)
    {
        // Ownership guard via the related appointment
        $appointment = $emargement->appointment;
        if (!$appointment || $appointment->user_id !== auth()->id()) {
            abort(403);
        }

        // Rotate token + resend
        $emargement = $this->service->rotateToken($emargement);
        dispatch(new SendEmargementEmailJob($emargement));

        return back()->with('success', 'Lien de signature renvoyé.');
    }

    /**
     * Download the generated evidence PDF.
     */
    public function download(Emargement $emargement)
    {
        $path = $emargement->pdf_path; // e.g. "emargements/1-evidence.pdf"

        if (!$path || !Storage::disk('public')->exists($path)) {
            abort(404, 'PDF introuvable');
        }

        $filename = 'Emargement-' . $emargement->id . '.pdf';
        return Storage::disk('public')->download($path, $filename);
    }

    /**
     * Public: show signing form (by token).
     * Tries singular first (your existing setup), then plural.
     */
    public function showSignForm(string $token)
    {
        $em = Emargement::where('token', $token)->firstOrFail();

        if ($em->status === 'signed') {
            return view()->first(
                ['emargement.already-signed', 'emargements.already-signed'],
                ['em' => $em]
            );
        }

        if ($em->isExpired()) {
            $em->update(['status' => 'expired']);
            return view()->first(
                ['emargement.expired', 'emargements.expired'],
                ['em' => $em]
            );
        }

        return view()->first(
            ['emargement.sign', 'emargements.sign'],
            ['em' => $em]
        );
    }

    /**
     * Public: submit signature (checkbox or canvas) for a token.
     * Captures IP + User-Agent; delegates PDF regeneration to service.
     */
    public function submitSignature(Request $request, string $token)
    {
        $em = Emargement::where('token', $token)->firstOrFail();

        if (!$em->canSign()) {
            // Choose whichever route exists (singular first for compatibility)
            $routeName = Route::has('emargement.sign.form')
                ? 'emargement.sign.form'
                : (Route::has('emargements.sign.form') ? 'emargements.sign.form' : null);

            if ($routeName) {
                return redirect()->route($routeName, $token)
                    ->with('error', 'Lien invalide ou expiré.');
            }

            // Fallback: 404 if neither route exists
            abort(404, 'Lien invalide ou expiré.');
        }

        $mode = $request->input('mode', 'checkbox'); // 'checkbox' | 'canvas'
        $signaturePath = null;

        if ($mode === 'canvas' && $request->filled('signature_data')) {
            // data:image/png;base64,.... → store as file
            $data = $request->input('signature_data');
            if (str_starts_with($data, 'data:image')) {
                [$meta, $b64] = explode(',', $data, 2);
                $binary = base64_decode($b64);
                $file   = 'emargements/signatures/'.uniqid('sig_').'.png';
                Storage::disk('public')->put($file, $binary);
                $signaturePath = $file;
            }
        } else {
            // Checkbox path: must accept "confirmed"
            $request->validate(['confirmed' => 'accepted']);
        }

        // Capture IP + User-Agent (truncate to 512 chars as per migration)
        $ip = $request->ip();
        $ua = substr($request->userAgent() ?? '', 0, 512);

        // Mark signed + regenerate evidence PDF (service should update pdf_path)
        $this->service->markSigned($em, $signaturePath, $ip, $ua);

        return view()->first(
            ['emargement.thanks', 'emargements.thanks'],
            ['em' => $em]
        );
    }
}
