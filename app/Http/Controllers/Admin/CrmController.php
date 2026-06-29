<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use App\Models\CrmLeadActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CrmController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdmin();

        $filters = $this->validatedFilters($request);
        $leadQuery = $this->filteredLeadQuery($filters);

        $leads = (clone $leadQuery)
            ->with([
                'owner:id,name',
                'latestActivities' => fn ($query) => $query->limit(3),
            ])
            ->orderBy('stage')
            ->orderBy('pipeline_order')
            ->orderByDesc('updated_at')
            ->get();

        $columns = collect(CrmLead::STAGES)->map(function (array $stage, string $key) use ($leads) {
            $stageLeads = $leads->where('stage', $key)->values();

            return [
                'key' => $key,
                'label' => $stage['label'],
                'description' => $stage['description'],
                'accent' => $stage['accent'],
                'leads' => $stageLeads,
                'count' => $stageLeads->count(),
                'value' => $stageLeads->sum(fn (CrmLead $lead) => (float) $lead->estimated_value),
            ];
        });

        $metrics = $this->buildMetrics((clone $leadQuery));

        $recentActivities = CrmLeadActivity::query()
            ->with('lead:id,full_name,company,stage')
            ->whereHas('lead', fn (Builder $query) => $query->applyCrmFilters($filters))
            ->latest('occurred_at')
            ->latest()
            ->limit(18)
            ->get();

        $sourceOptions = CrmLead::query()
            ->whereNotNull('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source')
            ->merge(CrmLead::DEFAULT_SOURCES)
            ->filter()
            ->unique()
            ->values();

        $referralSourceOptions = CrmLead::query()
            ->whereNotNull('referral_source')
            ->distinct()
            ->orderBy('referral_source')
            ->pluck('referral_source')
            ->filter()
            ->values();

        $adminUsers = User::query()
            ->where('is_admin', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.crm.index', [
            'columns' => $columns,
            'filters' => $filters,
            'metrics' => $metrics,
            'recentActivities' => $recentActivities,
            'sourceOptions' => $sourceOptions,
            'referralSourceOptions' => $referralSourceOptions,
            'adminUsers' => $adminUsers,
            'stages' => CrmLead::STAGES,
            'stageLabels' => CrmLead::FRENCH_STAGE_LABELS,
            'licenseOptions' => CrmLead::LICENSE_OPTIONS,
            'activityTypes' => CrmLeadActivity::TYPES,
            'activityDirections' => CrmLeadActivity::DIRECTIONS,
        ]);
    }

    public function showLead(CrmLead $lead)
    {
        $this->authorizeAdmin();

        $lead->load([
            'owner:id,name',
            'creator:id,name',
            'activities' => fn ($query) => $query->with('user:id,name')->latest('occurred_at')->latest(),
        ]);

        $adminUsers = User::query()
            ->where('is_admin', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $sourceOptions = CrmLead::query()
            ->whereNotNull('source')
            ->distinct()
            ->orderBy('source')
            ->pluck('source')
            ->merge(CrmLead::DEFAULT_SOURCES)
            ->filter()
            ->unique()
            ->values();

        $referralSourceOptions = CrmLead::query()
            ->whereNotNull('referral_source')
            ->distinct()
            ->orderBy('referral_source')
            ->pluck('referral_source')
            ->filter()
            ->values();

        return view('admin.crm.show', [
            'lead' => $lead,
            'adminUsers' => $adminUsers,
            'sourceOptions' => $sourceOptions,
            'referralSourceOptions' => $referralSourceOptions,
            'stages' => CrmLead::STAGES,
            'stageLabels' => CrmLead::FRENCH_STAGE_LABELS,
            'licenseOptions' => CrmLead::LICENSE_OPTIONS,
            'activityTypes' => CrmLeadActivity::TYPES,
            'activityDirections' => CrmLeadActivity::DIRECTIONS,
        ]);
    }

    public function storeLead(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $payload = $this->validatedLeadPayload($request);
        $payload['created_by_user_id'] = auth()->id();
        $payload['pipeline_order'] = $this->nextPipelineOrder($payload['stage']);
        $payload = $this->applyLifecycleFields($payload);

        $lead = CrmLead::create($payload);

        if ($request->filled('activity_body')) {
            $this->createActivityFromRequest($request, $lead);
        }

        return redirect()
            ->route('admin.crm.leads.show', $lead)
            ->with('success', 'Lead cree et ajoute au tunnel CRM.');
    }

    public function updateLead(Request $request, CrmLead $lead): RedirectResponse
    {
        $this->authorizeAdmin();

        $payload = $this->validatedLeadPayload($request);
        $payload = $this->applyLifecycleFields($payload, $lead);

        $lead->update($payload);

        return redirect()
            ->route('admin.crm.leads.show', $lead)
            ->with('success', 'Lead mis a jour.');
    }

    public function moveLead(Request $request, CrmLead $lead)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'stage' => ['required', Rule::in(array_keys(CrmLead::STAGES))],
        ]);

        $payload = $this->applyLifecycleFields([
            'stage' => $validated['stage'],
            'pipeline_order' => $this->nextPipelineOrder($validated['stage']),
            'probability' => CrmLead::STAGES[$validated['stage']]['probability'],
        ], $lead);

        $lead->update($payload);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'stage' => $lead->stage,
                'stage_label' => $lead->stage_label,
            ]);
        }

        return back()->with('success', 'Etape du lead mise a jour.');
    }

    public function destroyLead(CrmLead $lead): RedirectResponse
    {
        $this->authorizeAdmin();

        $lead->delete();

        return redirect()
            ->route('admin.crm.index')
            ->with('success', 'Lead archive.');
    }

    public function storeActivity(Request $request, CrmLead $lead): RedirectResponse
    {
        $this->authorizeAdmin();

        $this->createActivityFromRequest($request, $lead);

        return redirect()
            ->route('admin.crm.leads.show', $lead)
            ->with('success', 'Point de contact ajoute a la timeline.');
    }

    public function destroyActivity(CrmLead $lead, CrmLeadActivity $activity): RedirectResponse
    {
        $this->authorizeAdmin();

        abort_unless((int) $activity->crm_lead_id === (int) $lead->id, 404);

        $activity->delete();

        return redirect()
            ->route('admin.crm.index')
            ->with('success', 'Point de contact supprime.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();

        $filters = $this->validatedFilters($request);
        $query = $this->filteredLeadQuery($filters)
            ->with('owner:id,name')
            ->orderBy('stage')
            ->orderBy('pipeline_order')
            ->orderBy('created_at');

        $filename = 'crm_leads_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($query) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, [
                'id',
                'full_name',
                'company',
                'email',
                'phone',
                'source',
                'referral_source',
                'expected_license_type',
                'actual_license_type',
                'stage',
                'arr',
                'probability',
                'expected_close_date',
                'next_follow_up_at',
                'last_touch_at',
                'converted_at',
                'lost_at',
                'owner',
                'tags',
                'notes',
                'created_at',
            ]);

            $query->chunk(500, function ($leads) use ($out) {
                foreach ($leads as $lead) {
                    fputcsv($out, [
                        $lead->id,
                        $lead->full_name,
                        $lead->company,
                        $lead->email,
                        $lead->phone,
                        $lead->source,
                        $lead->referral_source,
                        $lead->expected_license_type,
                        $lead->actual_license_type,
                        $lead->stage,
                        $lead->arr,
                        $lead->probability,
                        optional($lead->expected_close_date)->format('Y-m-d'),
                        optional($lead->next_follow_up_at)->toDateTimeString(),
                        optional($lead->last_touch_at)->toDateTimeString(),
                        optional($lead->converted_at)->toDateTimeString(),
                        optional($lead->lost_at)->toDateTimeString(),
                        optional($lead->owner)->name,
                        $lead->tags_as_text,
                        $lead->notes,
                        optional($lead->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:8192'],
            'update_existing' => ['nullable', 'boolean'],
        ]);

        $handle = fopen($validated['csv_file']->getRealPath(), 'r');

        if (! $handle) {
            return back()->withErrors(['csv_file' => 'Impossible de lire le fichier CSV.']);
        }

        $firstLine = fgets($handle) ?: '';
        rewind($handle);

        $delimiter = $this->detectDelimiter($firstLine);
        $headers = fgetcsv($handle, 0, $delimiter);

        if (! $headers) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Le fichier CSV est vide.']);
        }

        $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $headers);

        if (! $this->hasAnyHeader($headers, ['full_name', 'name', 'lead_name', 'email'])) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Le CSV doit contenir au moins full_name, name, lead_name ou email.']);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($handle, $delimiter, $headers, $validated, &$created, &$updated, &$skipped) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if ($this->rowIsBlank($row)) {
                    continue;
                }

                $row = array_slice(array_pad($row, count($headers), null), 0, count($headers));
                $record = array_combine($headers, $row);
                $payload = $this->leadPayloadFromCsv($record);

                if (! $payload) {
                    $skipped++;
                    continue;
                }

                $existing = null;
                if (! empty($validated['update_existing']) && ! empty($payload['email'])) {
                    $existing = CrmLead::where('email', $payload['email'])->first();
                }

                if ($existing) {
                    $existing->update($this->applyLifecycleFields($payload, $existing));
                    $updated++;
                } else {
                    $payload['created_by_user_id'] = auth()->id();
                    $payload['pipeline_order'] = $this->nextPipelineOrder($payload['stage']);
                    CrmLead::create($this->applyLifecycleFields($payload));
                    $created++;
                }
            }
        });

        fclose($handle);

        return redirect()
            ->route('admin.crm.index')
            ->with('success', "Import termine : {$created} crees, {$updated} mis a jour, {$skipped} ignores.");
    }

    public function importTemplate(): StreamedResponse
    {
        $this->authorizeAdmin();

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($out, [
                'full_name',
                'company',
                'email',
                'phone',
                'source',
                'referral_source',
                'stage',
                'expected_license_type',
                'actual_license_type',
                'probability',
                'expected_close_date',
                'next_follow_up_at',
                'tags',
                'notes',
            ]);
            fputcsv($out, [
                'Marie Dupont',
                'Cabinet Example',
                'marie@example.com',
                '+33123456789',
                'Website',
                'Parrainage partenaire',
                'new',
                'new_pro_annuelle',
                '',
                '10',
                now()->addMonth()->toDateString(),
                now()->addWeek()->format('Y-m-d H:i:s'),
                'hot, priority',
                'Interested in a discovery call.',
            ]);
            fclose($out);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="crm_import_template.csv"',
        ]);
    }

    private function authorizeAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403);
    }

    private function validatedFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'stage' => ['nullable', Rule::in(array_keys(CrmLead::STAGES))],
            'source' => ['nullable', 'string', 'max:255'],
            'referral_source' => ['nullable', 'string', 'max:255'],
            'expected_license_type' => ['nullable', Rule::in(array_keys(CrmLead::LICENSE_OPTIONS))],
            'actual_license_type' => ['nullable', Rule::in(array_keys(CrmLead::LICENSE_OPTIONS))],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'touch_from' => ['nullable', 'date'],
            'touch_to' => ['nullable', 'date'],
            'follow_from' => ['nullable', 'date'],
            'follow_to' => ['nullable', 'date'],
        ]);

        return collect($validated)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }

    private function filteredLeadQuery(array $filters): Builder
    {
        return CrmLead::query()->applyCrmFilters($filters);
    }

    private function buildMetrics(Builder $query): array
    {
        $leads = $query->get([
            'id',
            'stage',
            'estimated_value',
            'next_follow_up_at',
            'converted_at',
            'created_at',
        ]);

        $openLeads = $leads->whereNotIn('stage', ['won', 'lost']);
        $convertedLeads = $leads->where('stage', 'won');
        $dueFollowUps = $openLeads->filter(fn (CrmLead $lead) => $lead->next_follow_up_at && $lead->next_follow_up_at->lte(now()));

        return [
            'total' => $leads->count(),
            'open' => $openLeads->count(),
            'pipeline_value' => $openLeads->sum(fn (CrmLead $lead) => (float) $lead->estimated_value),
            'won' => $convertedLeads->count(),
            'won_value' => $convertedLeads->sum(fn (CrmLead $lead) => (float) $lead->estimated_value),
            'due_followups' => $dueFollowUps->count(),
        ];
    }

    private function validatedLeadPayload(Request $request): array
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:255'],
            'referral_source' => ['nullable', 'string', 'max:255'],
            'expected_license_type' => ['nullable', Rule::in(array_keys(CrmLead::LICENSE_OPTIONS))],
            'actual_license_type' => ['nullable', Rule::in(array_keys(CrmLead::LICENSE_OPTIONS))],
            'stage' => ['required', Rule::in(array_keys(CrmLead::STAGES))],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date' => ['nullable', 'date'],
            'next_follow_up_at' => ['nullable', 'date'],
            'lost_reason' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'owner_user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $stage = $validated['stage'];
        $validated['probability'] = $validated['probability'] ?? CrmLead::STAGES[$stage]['probability'];
        $validated['tags'] = $this->parseTags($validated['tags'] ?? null);
        $validated['estimated_value'] = $this->arrFromLicenseFields(
            $validated['expected_license_type'] ?? null,
            $validated['actual_license_type'] ?? null
        );

        return $this->nullEmptyStrings($validated);
    }

    private function createActivityFromRequest(Request $request, CrmLead $lead): CrmLeadActivity
    {
        $validated = $request->validate([
            'activity_type' => ['nullable', Rule::in(array_keys(CrmLeadActivity::TYPES))],
            'activity_direction' => ['nullable', Rule::in(array_keys(CrmLeadActivity::DIRECTIONS))],
            'activity_subject' => ['nullable', 'string', 'max:255'],
            'activity_body' => ['required', 'string', 'max:5000'],
            'activity_occurred_at' => ['nullable', 'date'],
            'activity_due_at' => ['nullable', 'date'],
            'activity_completed' => ['nullable', 'boolean'],
            'activity_outcome' => ['nullable', 'string', 'max:255'],
        ]);

        $occurredAt = isset($validated['activity_occurred_at'])
            ? Carbon::parse($validated['activity_occurred_at'])
            : now();

        $activity = $lead->activities()->create([
            'user_id' => auth()->id(),
            'type' => $validated['activity_type'] ?? 'note',
            'direction' => $validated['activity_direction'] ?? 'internal',
            'subject' => $validated['activity_subject'] ?? null,
            'body' => $validated['activity_body'],
            'occurred_at' => $occurredAt,
            'due_at' => isset($validated['activity_due_at']) ? Carbon::parse($validated['activity_due_at']) : null,
            'completed_at' => ! empty($validated['activity_completed']) ? now() : null,
            'outcome' => $validated['activity_outcome'] ?? null,
        ]);

        $lead->forceFill([
            'last_touch_at' => $occurredAt,
            'next_follow_up_at' => $activity->due_at ?: $lead->next_follow_up_at,
        ])->save();

        return $activity;
    }

    private function applyLifecycleFields(array $payload, ?CrmLead $lead = null): array
    {
        $stage = $payload['stage'] ?? $lead?->stage ?? 'new';

        if ($stage === 'won') {
            $payload['converted_at'] = $lead?->converted_at ?: now();
            $payload['lost_at'] = null;
            $payload['lost_reason'] = null;
            $payload['probability'] = 100;
        } elseif ($stage === 'lost') {
            $payload['lost_at'] = $lead?->lost_at ?: now();
            $payload['converted_at'] = null;
            $payload['probability'] = 0;
        } else {
            $payload['converted_at'] = null;
            $payload['lost_at'] = null;
            $payload['lost_reason'] = null;
        }

        return $payload;
    }

    private function nextPipelineOrder(string $stage): int
    {
        return ((int) CrmLead::where('stage', $stage)->max('pipeline_order')) + 1;
    }

    private function arrFromLicenseFields(?string $expectedLicense, ?string $actualLicense): float
    {
        return CrmLead::arrForLicense($actualLicense ?: $expectedLicense);
    }

    private function parseTags(?string $value): array
    {
        if (! $value) {
            return [];
        }

        return collect(explode(',', $value))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->take(20)
            ->values()
            ->all();
    }

    private function nullEmptyStrings(array $values): array
    {
        return collect($values)
            ->map(fn ($value) => $value === '' ? null : $value)
            ->all();
    }

    private function normalizeHeader(string $header): string
    {
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
        $header = strtolower(trim($header));

        return preg_replace('/[^a-z0-9]+/', '_', $header) ?: $header;
    }

    private function hasAnyHeader(array $headers, array $needles): bool
    {
        return count(array_intersect($headers, $needles)) > 0;
    }

    private function rowIsBlank(array $row): bool
    {
        return collect($row)->filter(fn ($value) => trim((string) $value) !== '')->isEmpty();
    }

    private function detectDelimiter(string $line): string
    {
        $delimiters = [',', ';', "\t"];
        $scores = [];

        foreach ($delimiters as $delimiter) {
            $scores[$delimiter] = count(str_getcsv($line, $delimiter));
        }

        arsort($scores);

        return array_key_first($scores) ?: ',';
    }

    private function leadPayloadFromCsv(array $record): ?array
    {
        $stage = $this->normalizeStage($this->csvValue($record, ['stage', 'status', 'pipeline_stage']) ?: 'new');
        $fullName = $this->csvValue($record, ['full_name', 'name', 'lead_name']);
        $email = $this->csvValue($record, ['email', 'email_address']);
        $company = $this->csvValue($record, ['company', 'organisation', 'organization']);

        if (! $fullName) {
            $fullName = $email ?: $company;
        }

        if (! $fullName) {
            return null;
        }

        $probability = $this->csvValue($record, ['probability']);
        $probability = is_numeric($probability) ? max(0, min(100, (int) $probability)) : CrmLead::STAGES[$stage]['probability'];
        $expectedLicense = $this->normalizeLicenseType($this->csvValue($record, ['expected_license_type', 'expected_license', 'licence_visee']));
        $actualLicense = $this->normalizeLicenseType($this->csvValue($record, ['actual_license_type', 'actual_license', 'licence_reelle']));
        $arr = $this->arrFromLicenseFields($expectedLicense, $actualLicense);

        if ($arr <= 0 && ! $expectedLicense && ! $actualLicense) {
            $arr = $this->parseMoney($this->csvValue($record, ['arr', 'estimated_value', 'value', 'amount'])) ?? 0;
        }

        return $this->nullEmptyStrings([
            'full_name' => $fullName,
            'company' => $company,
            'email' => $email,
            'phone' => $this->csvValue($record, ['phone', 'mobile', 'telephone']),
            'source' => $this->csvValue($record, ['source', 'lead_source']),
            'referral_source' => $this->csvValue($record, ['referral_source', 'referral', 'source_parrainage', 'parrain']),
            'expected_license_type' => $expectedLicense,
            'actual_license_type' => $actualLicense,
            'stage' => $stage,
            'estimated_value' => $arr,
            'probability' => $probability,
            'expected_close_date' => $this->parseDate($this->csvValue($record, ['expected_close_date', 'close_date'])),
            'next_follow_up_at' => $this->parseDate($this->csvValue($record, ['next_follow_up_at', 'follow_up', 'next_touch'])),
            'tags' => $this->parseTags($this->csvValue($record, ['tags', 'tag'])),
            'notes' => $this->csvValue($record, ['notes', 'note', 'message']),
        ]);
    }

    private function csvValue(array $record, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $record) && trim((string) $record[$key]) !== '') {
                return trim((string) $record[$key]);
            }
        }

        return null;
    }

    private function normalizeStage(?string $value): string
    {
        $stage = strtolower(trim((string) $value));
        $stage = str_replace([' ', '-'], '_', $stage);

        $aliases = [
            'lead' => 'new',
            'fresh' => 'new',
            'missing_contact' => 'need_contact_info',
            'need_contact' => 'need_contact_info',
            'needs_contact_info' => 'need_contact_info',
            'first_contact' => 'contacted',
            'contact' => 'contacted',
            'qualified' => 'presentation_ok',
            'qualified_lead' => 'presentation_ok',
            'presentation' => 'presentation_ok',
            'presentation_done' => 'presentation_ok',
            'proposal' => 'onboarding_ok',
            'onboarding' => 'onboarding_ok',
            'onboarding_done' => 'onboarding_ok',
            'trial' => 'free_trial',
            'free' => 'free_trial',
            'negotiation' => 'free_trial',
            'referencement' => 'referencement_gratuit',
            'referencement_free' => 'referencement_gratuit',
            'converted' => 'won',
            'client' => 'won',
            'closed_won' => 'won',
            'closed_lost' => 'lost',
        ];

        $stage = $aliases[$stage] ?? $stage;

        return array_key_exists($stage, CrmLead::STAGES) ? $stage : 'new';
    }

    private function normalizeLicenseType(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $license = strtolower(trim($value));
        $license = str_replace([' ', '-'], '_', $license);

        $aliases = [
            'gratuit' => 'new_free',
            'free' => 'new_free',
            'essai' => 'new_trial',
            'trial' => 'new_trial',
            'starter_mensuelle' => 'new_starter_mensuelle',
            'starter_monthly' => 'new_starter_mensuelle',
            'starter_annuelle' => 'new_starter_annuelle',
            'starter_yearly' => 'new_starter_annuelle',
            'pro_mensuelle' => 'new_pro_mensuelle',
            'pro_monthly' => 'new_pro_mensuelle',
            'pro_annuelle' => 'new_pro_annuelle',
            'pro_yearly' => 'new_pro_annuelle',
            'premium_mensuelle' => 'new_premium_mensuelle',
            'premium_monthly' => 'new_premium_mensuelle',
            'premium_annuelle' => 'new_premium_annuelle',
            'premium_yearly' => 'new_premium_annuelle',
        ];

        $license = $aliases[$license] ?? $license;

        return array_key_exists($license, CrmLead::LICENSE_OPTIONS) ? $license : null;
    }

    private function parseMoney(?string $value): ?float
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = preg_replace('/[^\d,.\-]/', '', $value) ?? '';

        if (str_contains($value, ',') && ! str_contains($value, '.')) {
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }

        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
