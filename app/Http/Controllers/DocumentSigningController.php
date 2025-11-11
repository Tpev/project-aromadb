<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentSigning;
use App\Models\DocumentSignEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentSigningController extends Controller
{
    public function showForm(string $token)
    {
        $signing = DocumentSigning::with('document.clientProfile', 'document.owner')
            ->where('token', $token)
            ->firstOrFail();

        // Expiration guard
        abort_if(now()->greaterThan($signing->expires_at), 410, 'Lien expiré');

        $doc  = $signing->document;
        $role = $signing->current_role; // 'client' or 'therapist'
		// ❌ Prevent the owner (therapist) from signing in place of the client
		if ($role === 'client' && \Illuminate\Support\Facades\Auth::id() === $doc->owner_user_id) {
			abort(403, 'Le praticien ne peut pas signer à la place du client.');
}

        // If therapist must sign, require auth AND ownership
        if ($role === 'therapist') {
            abort_unless(Auth::check(), 403, 'Authentification requise.');
            abort_unless($doc->owner_user_id === Auth::id(), 403, 'Accès refusé.');
        }

        // Blade: resources/views/documents/sign/form.blade.php
        return view('documents.sign.form', [
            'doc'     => $doc,
            'signing' => $signing,
            'role'    => $role,
        ]);
    }

    public function submit(Request $request, string $token)
    {
        $signing = DocumentSigning::with('document.owner')->where('token', $token)->firstOrFail();
        abort_if(now()->greaterThan($signing->expires_at), 410, 'Lien expiré');

        $doc  = $signing->document;
        $role = $signing->current_role; // 'client' or 'therapist'

        // Therapist step: auth + ownership
        if ($role === 'therapist') {
            abort_unless(Auth::check(), 403, 'Authentification requise.');
            abort_unless($doc->owner_user_id === Auth::id(), 403, 'Accès refusé.');
        }

        // Handle signature mode (checkbox or canvas)
        $mode   = $request->input('mode', 'checkbox');
        $sigPng = null;

        if ($mode === 'canvas' && $request->filled('signature_data')) {
            // store base64 PNG
            $payload = $request->input('signature_data');
            if (str_contains($payload, ',')) {
                [, $b64] = explode(',', $payload, 2);
            } else {
                $b64 = $payload;
            }
            $bin     = base64_decode($b64);
            $sigDir  = 'documents/signatures';
            $sigPng  = $sigDir.'/'.uniqid($role.'_').'.png';
            Storage::disk('public')->put($sigPng, $bin);
        } else {
            // checkbox confirmation
            $request->validate(['confirmed' => 'accepted']);
        }

        // Write audit trail
        DocumentSignEvent::create([
            'document_id'          => $doc->id,
            'role'                 => $role, // client|therapist
            'signed_at'            => now(),
            'signer_ip'            => $request->ip(),
            'signer_user_agent'    => $request->userAgent(),
            'signature_image_path' => $sigPng,
        ]);

        if ($role === 'client') {
            // move to therapist step
            $signing->update([
                'current_role' => 'therapist',
                'status'       => 'partially_signed',
                'expires_at'   => now()->addDays(14),
            ]);
            $doc->update(['status' => 'partially_signed']);

            // Thank-you page for client
            return view('documents.sign.thanks');
        }

        // Therapist completes the flow
        $signing->update(['status' => 'signed']);
        $doc->update(['status' => 'signed']);

        // Build final signed PDF (original + evidence page)
        // (This should NOT try to touch documents.signed_at anymore)
        app(\App\Services\DocumentSigningService::class)->generateFinalPdf($doc);
        $doc->refresh();

        // Same simple thank-you page
        return view('documents.sign.thanks');
    }

public function send(Request $request, Document $doc)
{
    // Only the therapist (owner) can send
    if (!auth()->check() || auth()->id() !== $doc->owner_user_id) {
        abort(403, 'Accès non autorisé');
    }

    // Create or refresh the signing token
    $signing = \App\Models\DocumentSigning::updateOrCreate(
        ['document_id' => $doc->id],
        [
            'token'        => bin2hex(random_bytes(32)),
            'current_role' => 'client',
            'status'       => 'sent',
            'expires_at'   => now()->addDays(14),
        ]
    );

    // ✅ Update the document’s own status
    $doc->update(['status' => 'sent']);

    // TODO: send mail to client with route('documents.sign.form', $signing->token)
    return back()->with('success', 'Lien de signature envoyé au client.');
}


    public function resend(DocumentSigning $signing)
    {
        $doc = $signing->document;

        // simple ownership check
        abort_unless(Auth::check(), 403, 'Authentification requise.');
        abort_unless($doc->owner_user_id === Auth::id(), 403, 'Accès refusé.');

        $signing->update([
            'token'      => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(14),
        ]);

        // Re-send email if you have a Mailable here

        return back()->with('success', 'Nouveau lien de signature envoyé.');
    }
}
		