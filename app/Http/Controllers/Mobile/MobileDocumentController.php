<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Mail\DocumentSignRequestMail;
use App\Mail\TherapistFileUploadedToClientMail;
use App\Models\ClientFile;
use App\Models\ClientProfile;
use App\Models\Document;
use App\Models\DocumentSigning;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class MobileDocumentController extends Controller
{
    public function index()
    {
        $clients = ClientProfile::query()
            ->withCount('clientFiles')
            ->where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $documentCounts = Document::query()
            ->where('owner_user_id', Auth::id())
            ->selectRaw('client_profile_id, count(*) as aggregate')
            ->groupBy('client_profile_id')
            ->pluck('aggregate', 'client_profile_id');

        $pendingCounts = Document::query()
            ->where('owner_user_id', Auth::id())
            ->whereIn('status', ['sent', 'partially_signed'])
            ->selectRaw('client_profile_id, count(*) as aggregate')
            ->groupBy('client_profile_id')
            ->pluck('aggregate', 'client_profile_id');

        return view('mobile.documents.index', [
            'clients' => $clients,
            'documentCounts' => $documentCounts,
            'pendingCounts' => $pendingCounts,
            'filesTotal' => $clients->sum('client_files_count'),
            'documentsTotal' => $documentCounts->sum(),
            'pendingTotal' => $pendingCounts->sum(),
        ]);
    }

    public function showClient(ClientProfile $clientProfile)
    {
        $this->ensureOwnsClient($clientProfile);

        $clientProfile->load([
            'clientFiles' => fn ($query) => $query->latest(),
            'appointments.product',
        ]);

        $documents = Document::query()
            ->with(['signing', 'signEvents'])
            ->where('owner_user_id', Auth::id())
            ->where('client_profile_id', $clientProfile->id)
            ->latest()
            ->get();

        return view('mobile.documents.show', [
            'clientProfile' => $clientProfile,
            'documents' => $documents,
            'appointments' => $clientProfile->appointments->sortByDesc('appointment_date'),
        ]);
    }

    public function storeFile(Request $request, ClientProfile $clientProfile)
    {
        $this->ensureOwnsClient($clientProfile);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:204800'],
        ]);

        $uploadedFile = $data['file'];
        $path = $uploadedFile->store("client_files/{$clientProfile->id}", 'public');

        $clientFile = $clientProfile->clientFiles()->create([
            'file_path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);

        if ($clientProfile->hasEspaceClient() && $clientProfile->email) {
            Mail::to($clientProfile->email)->queue(
                new TherapistFileUploadedToClientMail($clientProfile, $clientFile)
            );
        }

        return redirect()
            ->route('mobile.documents.client', $clientProfile)
            ->with('success', 'Fichier importe.');
    }

    public function downloadFile(ClientProfile $clientProfile, ClientFile $file)
    {
        $this->ensureOwnsClient($clientProfile);
        abort_unless((int) $file->client_profile_id === (int) $clientProfile->id, 403);

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    public function destroyFile(ClientProfile $clientProfile, ClientFile $file)
    {
        $this->ensureOwnsClient($clientProfile);
        abort_unless((int) $file->client_profile_id === (int) $clientProfile->id, 403);

        Storage::disk('public')->delete($file->file_path);
        $file->delete();

        return redirect()
            ->route('mobile.documents.client', $clientProfile)
            ->with('success', 'Fichier supprime.');
    }

    public function storeSignatureDocument(Request $request, ClientProfile $clientProfile)
    {
        $this->ensureOwnsClient($clientProfile);

        $data = $request->validate([
            'file' => ['required', 'mimes:pdf', 'max:20480'],
            'appointment_id' => ['nullable', 'integer', 'exists:appointments,id'],
        ]);

        $appointmentId = $data['appointment_id'] ?? null;
        if ($appointmentId) {
            $ownsAppointment = $clientProfile->appointments()
                ->where('id', $appointmentId)
                ->where('user_id', Auth::id())
                ->exists();

            abort_unless($ownsAppointment, 403);
        }

        $uploadedFile = $data['file'];
        $path = $uploadedFile->store('documents/originals', 'public');

        $document = Document::create([
            'owner_user_id' => Auth::id(),
            'client_profile_id' => $clientProfile->id,
            'appointment_id' => $appointmentId,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'storage_path' => $path,
            'uploaded_by_user_id' => Auth::id(),
            'status' => 'draft',
        ]);

        return redirect()
            ->route('mobile.documents.client', $clientProfile)
            ->with('success', 'Document importe.')
            ->with('new_document_id', $document->id);
    }

    public function sendSignatureDocument(Document $document)
    {
        $this->ensureOwnsDocument($document);
        $document->load('clientProfile');

        abort_unless($document->clientProfile?->email, 422, 'Email client manquant.');
        abort_unless($document->status === 'draft', 422, 'Document deja envoye.');

        $signing = DocumentSigning::updateOrCreate(
            ['document_id' => $document->id],
            [
                'token' => bin2hex(random_bytes(32)),
                'current_role' => 'client',
                'status' => 'sent',
                'expires_at' => now()->addDays(14),
            ]
        );

        $document->update(['status' => 'sent']);

        Mail::to($document->clientProfile->email)->queue(
            new DocumentSignRequestMail($document, $signing, $this->clientName($document->clientProfile))
        );

        return redirect()
            ->route('mobile.documents.client', $document->clientProfile)
            ->with('success', 'Lien de signature envoye.');
    }

    public function resendSignature(DocumentSigning $signing)
    {
        $signing->load('document.clientProfile');
        $document = $signing->document;

        $this->ensureOwnsDocument($document);
        abort_unless($document->clientProfile?->email, 422, 'Email client manquant.');

        $signing->update([
            'token' => bin2hex(random_bytes(32)),
            'expires_at' => now()->addDays(14),
            'status' => 'sent',
        ]);

        $document->update(['status' => 'sent']);

        Mail::to($document->clientProfile->email)->queue(
            new DocumentSignRequestMail($document, $signing, $this->clientName($document->clientProfile))
        );

        return redirect()
            ->route('mobile.documents.client', $document->clientProfile)
            ->with('success', 'Lien de signature renvoye.');
    }

    public function downloadOriginal(Document $document)
    {
        $this->ensureOwnsDocument($document);

        return Storage::disk('public')->download($document->storage_path, $document->original_name);
    }

    public function downloadFinal(Document $document)
    {
        $this->ensureOwnsDocument($document);

        abort_if(! $document->final_pdf_path || ! Storage::disk('public')->exists($document->final_pdf_path), 404);

        return Storage::disk('public')->download($document->final_pdf_path, 'Document-signe-' . $document->id . '.pdf');
    }

    private function ensureOwnsClient(ClientProfile $clientProfile): void
    {
        abort_unless((int) $clientProfile->user_id === (int) Auth::id(), 403);
    }

    private function ensureOwnsDocument(Document $document): void
    {
        abort_unless((int) $document->owner_user_id === (int) Auth::id(), 403);
    }

    private function clientName(?ClientProfile $clientProfile): ?string
    {
        if (! $clientProfile) {
            return null;
        }

        $name = trim(($clientProfile->first_name ?? '') . ' ' . ($clientProfile->last_name ?? ''));

        return $name !== '' ? $name : null;
    }
}
