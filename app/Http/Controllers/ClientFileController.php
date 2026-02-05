<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\ClientFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientFileController extends Controller
{
    public function clientUpload(Request $request)
    {
        $clientProfile = auth('client')->user(); // ClientProfile is the user model

        $data = $request->validate([
            'document' => 'required|file|max:20480', // 20MB
        ]);

        $uploadedFile = $data['document'];

        // Store on PUBLIC disk
        $path = $uploadedFile->store("client_files/{$clientProfile->id}", 'public');

        $clientProfile->clientFiles()->create([
            'file_path'     => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type'     => $uploadedFile->getMimeType(),
            'size'          => $uploadedFile->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Fichier enregistré',
            'path'    => $path,
        ]);
    }

    /**
     * Store the uploaded file in storage and record it in DB.
     * (Called from the upload form in the client profile show page)
     */
    public function store(Request $request, ClientProfile $clientProfile)
    {
        // Ownership check
        if ($clientProfile->user_id !== Auth::id()) {
            abort(403, 'Accès refusé.');
        }

        // Validate the incoming file
        $data = $request->validate([
            'file' => 'required|file|max:20480', // 20MB max
        ]);

        $uploadedFile = $data['file'];

        // Store on PUBLIC disk (consistent with the rest)
        $path = $uploadedFile->store("client_files/{$clientProfile->id}", 'public');

        // Save in DB
        $clientProfile->clientFiles()->create([
            'file_path'     => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type'     => $uploadedFile->getMimeType(),
            'size'          => $uploadedFile->getSize(),
        ]);

        return redirect()
            ->route('client_profiles.show', $clientProfile)
            ->with('success', 'Fichier téléchargé avec succès !');
    }

    /**
     * Download the file from storage (therapist/owner route).
     */
    public function download(ClientProfile $clientProfile, ClientFile $file)
    {
        // Ownership check
        if (
            $clientProfile->user_id !== Auth::id() ||
            $file->client_profile_id !== $clientProfile->id
        ) {
            abort(403, 'Accès refusé.');
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    public function downloadClient(ClientFile $file)
    {
        $client = auth('client')->user();

        if ($file->client_profile_id !== $client->id) {
            abort(403, 'Accès refusé.');
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    public function downloadForTherapist(ClientProfile $clientProfile, ClientFile $file)
    {
        // Check file belongs to this profile
        if ($file->client_profile_id !== $clientProfile->id) {
            abort(403, 'Fichier invalide.');
        }

        // Only allow the assigned therapist
        if ($clientProfile->user_id !== auth()->id()) {
            abort(403, 'Accès refusé.');
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    /**
     * Delete a file from the database & storage,
     * then redirect back to the client profile show page.
     */
    public function destroy(ClientProfile $clientProfile, ClientFile $file)
    {
        // Ownership check
        if (
            $clientProfile->user_id !== Auth::id() ||
            $file->client_profile_id !== $clientProfile->id
        ) {
            abort(403, 'Accès refusé.');
        }

        // Remove the file from storage
        Storage::disk('public')->delete($file->file_path);

        // Remove the record from the database
        $file->delete();

        return redirect()
            ->route('client_profiles.show', $clientProfile)
            ->with('success', 'Fichier supprimé avec succès !');
    }
}
