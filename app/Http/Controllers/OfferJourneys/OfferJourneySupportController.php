<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyDeliverabilityEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneySenderControl;
use App\Domain\OfferJourneys\Models\OfferJourneySupportAudit;
use App\Domain\OfferJourneys\Services\OfferJourneyDiagnosticLabels;
use App\Domain\OfferJourneys\Services\OfferJourneyDnsDiagnostic;
use App\Domain\OfferJourneys\Services\OfferJourneySafeRetry;
use App\Domain\OfferJourneys\Services\OfferJourneySupportAuditLogger;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessOfferJourneyAutomationRun;
use App\Jobs\RunOfferJourneyReconciliation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferJourneySupportController extends Controller
{
    public function index(Request $request, OfferJourneyDnsDiagnostic $dns, OfferJourneyDiagnosticLabels $labels): View
    {
        $this->authorizeSupport($request);
        $query = trim((string) $request->query('q', ''));
        $since = now()->subDays(30);
        $sent = OfferJourneyMessageDelivery::query()->whereNotNull('sent_at')->where('sent_at', '>=', $since)->count();
        $eventCounts = OfferJourneyDeliverabilityEvent::query()
            ->where('occurred_at', '>=', $since)
            ->select('event_type', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('event_type')
            ->pluck('aggregate', 'event_type');

        $results = ['practitioners' => collect(), 'journeys' => collect(), 'campaigns' => collect(), 'contacts' => collect(), 'runs' => collect(), 'deliveries' => collect()];
        if ($query !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $query).'%';
            $results['practitioners'] = User::query()->where('is_therapist', true)
                ->where(fn ($q) => $q->where('email', 'like', $like)->orWhere('name', 'like', $like)->orWhere('company_name', 'like', $like))
                ->limit(15)->get(['id', 'name', 'company_name', 'email']);
            $results['journeys'] = OfferJourney::query()->with('user:id,name,company_name,email')
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('slug', 'like', $like)->when(ctype_digit($query), fn ($q) => $q->orWhereKey((int) $query)))
                ->limit(15)->get();
            $results['contacts'] = OfferJourneyContact::query()->with('user:id,name,company_name,email')
                ->where(fn ($q) => $q->where('email', 'like', $like)->orWhere('first_name', 'like', $like)->orWhere('last_name', 'like', $like)->when(ctype_digit($query), fn ($q) => $q->orWhereKey((int) $query)))
                ->limit(15)->get();
            $results['campaigns'] = OfferJourneyMessageCampaign::query()->with(['user:id,name,company_name,email', 'journeys:id,name'])
                ->where(fn ($q) => $q->where('name', 'like', $like)->orWhere('subject', 'like', $like)->when(ctype_digit($query), fn ($q) => $q->orWhereKey((int) $query)))
                ->latest()->limit(15)->get();
            $results['runs'] = OfferJourneyAutomationRun::query()->with(['contact', 'automation.journey.user:id,name,company_name,email'])
                ->where(fn ($q) => $q->where('last_error', 'like', $like)->orWhere('exit_reason', 'like', $like)->when(ctype_digit($query), fn ($q) => $q->orWhereKey((int) $query)))
                ->limit(15)->get();
            $results['deliveries'] = OfferJourneyMessageDelivery::query()->with('user:id,name,company_name,email')
                ->where(fn ($q) => $q->where('recipient_email', 'like', $like)->orWhere('provider_message_id', 'like', $like)->orWhere('failure_reason', 'like', $like)->when(ctype_digit($query), fn ($q) => $q->orWhereKey((int) $query)))
                ->latest()->limit(15)->get();
        }

        return view('offer-journeys.admin.support', [
            'query' => $query,
            'results' => $results,
            'labels' => $labels,
            'metrics' => [
                'sent' => $sent,
                'delivered' => (int) ($eventCounts['delivery'] ?? 0),
                'bounced' => (int) ($eventCounts['bounce'] ?? 0),
                'complaints' => (int) ($eventCounts['complaint'] ?? 0),
                'rejected' => (int) ($eventCounts['reject'] ?? 0),
                'bounce_rate' => $sent > 0 ? round(100 * (int) ($eventCounts['bounce'] ?? 0) / $sent, 2) : 0,
                'complaint_rate' => $sent > 0 ? round(100 * (int) ($eventCounts['complaint'] ?? 0) / $sent, 3) : 0,
            ],
            'dns' => $dns->check($request->boolean('refresh_dns')),
            'recentEvents' => OfferJourneyDeliverabilityEvent::query()->with(['user:id,name,company_name', 'delivery:id,subject'])
                ->latest('occurred_at')->limit(20)->get(),
            'recentAudits' => OfferJourneySupportAudit::query()->with('actor:id,name,email')->latest('occurred_at')->limit(20)->get(),
        ]);
    }

    public function senderControl(Request $request, User $user, OfferJourneySupportAuditLogger $audit): RedirectResponse
    {
        $this->authorizeSupport($request);
        abort_unless($user->isTherapist(), 404);
        $data = $request->validate([
            'mode' => ['required', Rule::in(['marketing', 'all', 'resume'])],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        $control = OfferJourneySenderControl::query()->firstOrCreate(['user_id' => $user->id]);
        $before = $control->only(['marketing_paused', 'all_email_paused', 'pause_reason', 'paused_at']);
        $control->update([
            'marketing_paused' => $data['mode'] === 'marketing' || $data['mode'] === 'all',
            'all_email_paused' => $data['mode'] === 'all',
            'pause_reason' => $data['mode'] === 'resume' ? null : $data['reason'],
            'paused_by_user_id' => $request->user()->id,
            'paused_at' => $data['mode'] === 'resume' ? null : now(),
        ]);
        $audit->record($request->user(), 'sender_control.'.$data['mode'], $user, $data['reason'], $before, $control->fresh()->only(array_keys($before)), $request);

        return back()->with('success', $data['mode'] === 'resume' ? 'Envois retablis.' : 'Pause appliquee.');
    }

    public function pauseJourney(Request $request, OfferJourney $journey, OfferJourneySupportAuditLogger $audit): RedirectResponse
    {
        $this->authorizeSupport($request);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $before = $journey->only(['status', 'paused_at']);
        $journey->update(['status' => 'paused', 'paused_at' => now()]);
        $audit->record($request->user(), 'journey.pause', $journey, $data['reason'], $before, $journey->fresh()->only(array_keys($before)), $request);

        return back()->with('success', 'Parcours mis en pause.');
    }

    public function retryRun(Request $request, OfferJourneyAutomationRun $run, OfferJourneySafeRetry $retry, OfferJourneySupportAuditLogger $audit): RedirectResponse
    {
        $this->authorizeSupport($request);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:1000']]);
        $before = $run->only(['status', 'last_error', 'next_action_at']);
        $retry->retry($run);
        $audit->record($request->user(), 'automation_run.retry', $run, $data['reason'], $before, $run->fresh()->only(array_keys($before)), $request);
        ProcessOfferJourneyAutomationRun::dispatch($run->id);

        return back()->with('success', 'Relance sure placee dans la file d attente.');
    }

    public function reconcile(Request $request, OfferJourneySupportAuditLogger $audit): RedirectResponse
    {
        $this->authorizeSupport($request);
        $data = $request->validate([
            'days' => ['required', 'integer', 'between:1,365'],
            'dry_run' => ['nullable', 'boolean'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        RunOfferJourneyReconciliation::dispatch((int) $data['days'], (bool) ($data['dry_run'] ?? false));
        $audit->record($request->user(), 'reconciliation.dispatch', null, $data['reason'], null, ['days' => (int) $data['days'], 'dry_run' => (bool) ($data['dry_run'] ?? false)], $request);

        return back()->with('success', 'Reconciliation placee dans la file d attente.');
    }

    private function authorizeSupport(Request $request): void
    {
        abort_unless((bool) config('offer_journeys.support_console_enabled', false), 404);
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
