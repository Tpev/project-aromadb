<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentSignEvent;
use App\Models\DocumentSigning;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use iio\libmergepdf\Merger;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

class DocumentSigningService
{
    public function generateFinalPdf(Document $doc): void
    {
        // Guard: original must exist
        if (!$doc->storage_path || !Storage::disk('public')->exists($doc->storage_path)) {
            return;
        }

        // Gather signing data
        $doc->loadMissing(['clientProfile','owner']);
        $events = DocumentSignEvent::where('document_id', $doc->id)
            ->orderBy('signed_at')
            ->get();

        // Use the current (or latest) signing to show a token tail on the annex
        $signing = DocumentSigning::where('document_id', $doc->id)->latest()->first();
        $tokenTail = $signing ? '…'.Str::of($signing->token)->substr(-10) : '—';

        // Hash of original
        $hashOriginal = hash('sha256', Storage::disk('public')->get($doc->storage_path));

        // Render annex with YOUR blade name: documents.pdf_annex
        $evidenceHtml = view('documents.pdf_annex', [
            'doc'          => $doc,
            'client'       => $doc->clientProfile,
            'therapist'    => $doc->owner,
            'events'       => $events,
            'hashOriginal' => $hashOriginal,
            'hashFinal'    => null,        // filled after merge
            'tokenTail'    => $tokenTail,
        ])->render();

        // Make evidence PDF
        $tmpEvidenceDir  = 'documents/tmp';
        $tmpEvidenceName = 'evidence-'.$doc->id.'-'.time().'.pdf';
        $tmpEvidencePath = $tmpEvidenceDir.'/'.$tmpEvidenceName;
        Storage::disk('public')->makeDirectory($tmpEvidenceDir);

        $pdf = PDF::loadHTML($evidenceHtml)->setPaper('a4');
        Storage::disk('public')->put($tmpEvidencePath, $pdf->output());

        // Merge original + annex
        $finalDir  = 'documents/finals';
        $finalName = 'signed-'.$doc->id.'-'.time().'.pdf';
        $finalPath = $finalDir.'/'.$finalName;
        Storage::disk('public')->makeDirectory($finalDir);

        $merger = new Merger();
        $merger->addRaw(Storage::disk('public')->get($doc->storage_path));   // original
        $merger->addRaw(Storage::disk('public')->get($tmpEvidencePath));     // annex
        $merged = $merger->merge();

        Storage::disk('public')->put($finalPath, $merged);

        // Final hash + persist
        $hashFinal = hash('sha256', $merged);
        $doc->final_pdf_path = $finalPath;
        $doc->hash_original  = $hashOriginal;
        $doc->hash_final     = $hashFinal;
        $doc->save();

        // Optional: clean temp annex
        // Storage::disk('public')->delete($tmpEvidencePath);
    }
}
