<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyContactImport;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Domain\OfferJourneys\Services\OfferJourneyPipeline;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OfferJourneyContactImportController extends Controller
{
    public function index(Request $request): View
    {
        $this->available();
        $imports = OfferJourneyContactImport::query()->where('user_id', $request->user()->id)->latest()->limit(20)->get();

        return view('offer-journeys.practitioner.contacts.import', compact('imports'));
    }

    public function preview(Request $request): RedirectResponse
    {
        $this->available();
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:2048'],
            'consent_proof' => ['nullable', 'string', 'max:255'],
        ]);
        [$rows, $report, $hasConsent] = $this->parse((string) $validated['file']->getRealPath(), $request->user()->id);
        if ($hasConsent && blank($validated['consent_proof'] ?? null)) {
            return back()->withErrors(['consent_proof' => 'Indiquez la preuve de consentement avant d’importer des contacts à relancer.']);
        }

        $import = OfferJourneyContactImport::query()->create([
            'user_id' => $request->user()->id,
            'created_by_user_id' => $request->user()->id,
            'original_filename' => $validated['file']->getClientOriginalName(),
            'status' => 'preview',
            'consent_proof' => $validated['consent_proof'] ?? null,
            'rows_json' => $rows,
            'report_json' => $report,
        ]);

        return redirect()->route('offer-journeys.contacts.import.index', ['import' => $import->id])->with('success', 'Aperçu créé. Aucun contact ni consentement n’a encore été ajouté.');
    }

    public function commit(Request $request, OfferJourneyContactImport $import, OfferJourneyPipeline $pipeline): RedirectResponse
    {
        $this->available();
        $this->own($request, $import);
        abort_unless($import->status === 'preview', 422);
        $pipeline->ensureDefaults($request->user());
        $stageId = OfferJourneyPipelineStage::query()->where('user_id', $request->user()->id)->where('system_key', 'new')->value('id');
        $created = [];
        $skipped = 0;

        DB::transaction(function () use ($import, $request, $stageId, &$created, &$skipped) {
            foreach ($import->rows_json as $row) {
                if (OfferJourneyContact::query()->where('user_id', $request->user()->id)->where('email_normalized', $row['email'])->exists()) {
                    $skipped++;
                    continue;
                }
                $contact = OfferJourneyContact::query()->create([
                    'user_id' => $request->user()->id,
                    'pipeline_stage_id' => $stageId,
                    'email' => $row['email'],
                    'email_normalized' => $row['email'],
                    'first_name' => $row['first_name'] ?: null,
                    'last_name' => $row['last_name'] ?: null,
                    'phone' => $row['phone'] ?: null,
                    'phone_normalized' => $row['phone'] ? preg_replace('/\D+/', '', $row['phone']) : null,
                    'status' => 'new',
                    'metadata' => ['import_id' => $import->id, 'source' => 'csv_import'],
                    'last_activity_at' => now(),
                ]);
                $created[] = $contact->id;
                if ($row['marketing_consent']) {
                    $contact->consents()->create([
                        'purpose' => 'marketing_follow_up',
                        'status' => 'granted',
                        'legal_basis' => 'consent',
                        'text_version' => config('offer_journeys.legal.consent_text_version', 'draft-v1-legal-review-required'),
                        'text_snapshot' => 'Consentement importé. Preuve déclarée: '.$import->consent_proof,
                        'source' => 'csv_import',
                        'context_json' => ['import_id' => $import->id, 'proof' => $import->consent_proof],
                        'granted_at' => now(),
                    ]);
                }
            }
            $import->update([
                'status' => 'committed',
                'created_contact_ids_json' => $created,
                'committed_at' => now(),
                'report_json' => [...($import->report_json ?? []), 'created' => count($created), 'skipped_at_commit' => $skipped],
            ]);
        });

        return back()->with('success', count($created).' contact(s) importé(s), '.$skipped.' ignoré(s). Aucun message n’a été envoyé.');
    }

    public function rollback(Request $request, OfferJourneyContactImport $import): RedirectResponse
    {
        $this->available();
        $this->own($request, $import);
        abort_unless($import->status === 'committed', 422);
        $ids = $import->created_contact_ids_json ?? [];
        $hasSent = DB::table('offer_journey_message_deliveries')->whereIn('offer_journey_contact_id', $ids)->whereNotNull('sent_at')->exists();
        abort_if($hasSent, 422, 'Cet import ne peut plus être annulé car un message a déjà été envoyé.');

        DB::transaction(function () use ($ids, $import) {
            OfferJourneyContact::query()->whereIn('id', $ids)->whereNull('client_profile_id')->get()->each->forceDelete();
            $import->update(['status' => 'rolled_back', 'rolled_back_at' => now()]);
        });

        return back()->with('success', 'L’import a été annulé avant tout envoi.');
    }

    private function parse(string $path, int $userId): array
    {
        $handle = fopen($path, 'rb');
        $first = fgets($handle) ?: '';
        rewind($handle);
        $delimiter = substr_count($first, ';') >= substr_count($first, ',') ? ';' : ',';
        $headers = array_map(fn ($value) => $this->header((string) $value), fgetcsv($handle, 0, $delimiter) ?: []);
        $map = array_flip($headers);
        $emailIndex = $map['email'] ?? null;
        abort_if($emailIndex === null, 422, 'Le fichier doit contenir une colonne email.');

        $rows = [];
        $ignored = [];
        $seen = [];
        $line = 1;
        $hasConsent = false;
        while (($values = fgetcsv($handle, 0, $delimiter)) !== false && $line < 2001) {
            $line++;
            $email = Str::lower(trim((string) ($values[$emailIndex] ?? '')));
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $ignored[] = ['line' => $line, 'reason' => 'Adresse email invalide'];
                continue;
            }
            if (isset($seen[$email])) {
                $ignored[] = ['line' => $line, 'reason' => 'Doublon dans le fichier'];
                continue;
            }
            if (OfferJourneyContact::query()->where('user_id', $userId)->where('email_normalized', $email)->exists()) {
                $ignored[] = ['line' => $line, 'reason' => 'Contact déjà présent'];
                continue;
            }
            $seen[$email] = true;
            $consent = $this->truthy($values[$map['marketing_consent'] ?? -1] ?? null);
            $hasConsent = $hasConsent || $consent;
            $rows[] = [
                'email' => $email,
                'first_name' => trim((string) ($values[$map['first_name'] ?? -1] ?? '')),
                'last_name' => trim((string) ($values[$map['last_name'] ?? -1] ?? '')),
                'phone' => trim((string) ($values[$map['phone'] ?? -1] ?? '')),
                'marketing_consent' => $consent,
            ];
        }
        fclose($handle);

        return [$rows, ['valid' => count($rows), 'ignored' => $ignored, 'truncated' => $line >= 2001], $hasConsent];
    }

    private function header(string $header): string
    {
        $header = Str::of($header)->replace("\xEF\xBB\xBF", '')->ascii()->lower()->replace([' ', '-'], '_')->toString();

        return match ($header) {
            'prenom', 'first_name' => 'first_name',
            'nom', 'last_name' => 'last_name',
            'telephone', 'tel', 'phone' => 'phone',
            'consentement_marketing', 'marketing_consent', 'consentement' => 'marketing_consent',
            default => $header,
        };
    }

    private function truthy($value): bool
    {
        return in_array(Str::lower(trim((string) $value)), ['1', 'oui', 'yes', 'true', 'x'], true);
    }

    private function own(Request $request, OfferJourneyContactImport $import): void
    {
        abort_unless((int) $import->user_id === (int) $request->user()->id, 404);
    }

    private function available(): void
    {
        abort_unless(config('offer_journeys.contact_import_enabled', false), 404);
    }
}
