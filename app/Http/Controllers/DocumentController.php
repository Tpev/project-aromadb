<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{


public function store(Request $req, ClientProfile $clientProfile)
{
    $validated = $req->validate([
        'file'           => ['required','mimes:pdf','max:20480'], // 20MB
        'appointment_id' => ['nullable','integer','exists:appointments,id'],
    ]);

    $file = $validated['file'];
    $path = $file->store('documents/originals', 'public');
    $name = $file->getClientOriginalName();

    $doc = Document::create([
        'owner_user_id'       => $req->user()->id,
        'client_profile_id'   => $clientProfile->id,   // <— correct
        'appointment_id'      => $validated['appointment_id'] ?? null,
        'original_name'       => $name,
        'storage_path'        => $path,
        'uploaded_by_user_id' => $req->user()->id,
        'status'              => 'draft',
    ]);

    return back()->with('success', 'Document importé.')->with('new_document_id', $doc->id);
}


    public function downloadFinal(Document $doc)
    {
        Gate::authorize('view', $doc);
        abort_if(!$doc->final_pdf_path || !Storage::disk('public')->exists($doc->final_pdf_path), 404);
        return Storage::disk('public')->download($doc->final_pdf_path, 'Document-signé-'.$doc->id.'.pdf');
    }
}
