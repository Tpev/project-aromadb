<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterImport;
use App\Models\User;
use App\Services\NewsletterImportService;
use Illuminate\Http\Request;

class NewsletterImportController extends Controller
{
    public function index(Request $request, User $therapist)
    {
        $this->authorizeAdmin($request, $therapist);

        return view('admin.newsletter-imports.index', [
            'therapist' => $therapist,
            'imports' => NewsletterImport::where('user_id', $therapist->id)->with('creator')->latest()->paginate(20),
        ]);
    }

    public function preview(Request $request, User $therapist, NewsletterImportService $service)
    {
        $this->authorizeAdmin($request, $therapist);
        $data = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:4096'],
            'audience_name' => ['required', 'string', 'max:255'],
        ]);
        $contents = file_get_contents($data['csv_file']->getRealPath());
        $hash = hash('sha256', $contents);
        $import = NewsletterImport::where('user_id', $therapist->id)->where('file_hash', $hash)->first();
        if (! $import) {
            $parsed = $service->parse($contents);
            $review = $service->review($parsed['rows'], $therapist->id);
            $import = NewsletterImport::firstOrCreate(['user_id' => $therapist->id, 'file_hash' => $hash], [
                'created_by_user_id' => $request->user()->id, 'audience_name' => trim($data['audience_name']),
                'original_filename' => mb_substr($data['csv_file']->getClientOriginalName(), 0, 255),
                'rows' => $parsed['rows'],
                'report' => $review['report'] + ['encoding' => $parsed['encoding'], 'delimiter' => $parsed['delimiter']],
            ]);
        }

        return redirect()->route('admin.therapists.newsletter-imports.show', [$therapist, $import]);
    }

    public function show(Request $request, User $therapist, NewsletterImport $import, NewsletterImportService $service)
    {
        $this->authorizeAdmin($request, $therapist, $import);
        $review = $import->status === 'completed'
            ? ['rows' => $import->rows, 'report' => $import->report]
            : $service->review($import->rows, $therapist->id);
        $page = max(1, (int) $request->query('page', 1));
        $rows = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($review['rows'], ($page - 1) * 100, 100), count($review['rows']), 100, $page,
            ['path' => $request->url()]
        );

        return view('admin.newsletter-imports.show', [
            'therapist' => $therapist, 'import' => $import->load('creator'), 'rows' => $rows, 'report' => $review['report'],
        ]);
    }

    public function commit(Request $request, User $therapist, NewsletterImport $import, NewsletterImportService $service)
    {
        $this->authorizeAdmin($request, $therapist, $import);
        $request->validate(['confirm' => ['accepted']]);
        $service->commit($import);

        return redirect()->route('admin.therapists.newsletter-imports.show', [$therapist, $import])
            ->with('success', 'Import terminé. Aucun email n’a été envoyé.');
    }

    private function authorizeAdmin(Request $request, User $therapist, ?NewsletterImport $import = null): void
    {
        abort_unless($request->user()?->isAdmin(), 403);
        abort_unless($therapist->is_therapist, 404);
        abort_if($import && (int) $import->user_id !== (int) $therapist->id, 404);
    }
}
