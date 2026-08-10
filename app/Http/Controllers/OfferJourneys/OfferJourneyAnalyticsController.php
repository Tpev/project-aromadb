<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Services\OfferJourneyWorkspace;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class OfferJourneyAnalyticsController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request, OfferJourney $journey, OfferJourneyWorkspace $workspace): View
    {
        $this->authorize('view', $journey);
        $days = in_array((int) $request->query('days', 30), [7, 30, 90, 365], true)
            ? (int) $request->query('days', 30)
            : 30;
        $from = Carbon::now()->subDays($days)->startOfDay();

        $events = OfferJourneyEvent::query()
            ->where('offer_journey_id', $journey->id)
            ->where('occurred_at', '>=', $from)
            ->where('is_test', false)
            ->where('is_bot', false);
        $entries = OfferJourneyEntry::query()
            ->where('offer_journey_id', $journey->id)
            ->where('entered_at', '>=', $from);
        $conversions = $journey->conversions()
            ->where('status', 'confirmed')
            ->where('occurred_at', '>=', $from);
        $formSubmissions = (clone $events)->where('event_name', 'lead_captured')->count();
        $uniqueContacts = (clone $entries)->distinct()->count('offer_journey_contact_id');

        $metrics = [
            'views' => (clone $events)->where('event_name', 'page_viewed')->count(),
            'visitors' => (clone $events)->where('event_name', 'page_viewed')->whereNotNull('session_id')->distinct('session_id')->count('session_id'),
            'form_submissions' => $formSubmissions,
            'unique_contacts' => $uniqueContacts,
            'new_contacts' => OfferJourneyContact::query()
                ->where('user_id', $journey->user_id)
                ->where('created_at', '>=', $from)
                ->whereHas('entries', fn ($query) => $query->where('offer_journey_id', $journey->id))
                ->count(),
            'leads' => $formSubmissions,
            'cta_clicks' => (clone $events)->where('event_name', 'primary_cta_clicked')->count(),
            'conversions' => (clone $conversions)->count(),
            'revenue_cents' => (int) (clone $conversions)->sum('amount_cents'),
        ];
        $metrics['submission_rate'] = $metrics['visitors'] > 0
            ? round(($metrics['form_submissions'] / $metrics['visitors']) * 100, 1)
            : 0.0;
        $metrics['contact_rate'] = $metrics['visitors'] > 0
            ? round(($metrics['unique_contacts'] / $metrics['visitors']) * 100, 1)
            : 0.0;
        $metrics['form_to_contact_rate'] = $metrics['form_submissions'] > 0
            ? round(($metrics['unique_contacts'] / $metrics['form_submissions']) * 100, 1)
            : 0.0;
        $metrics['contact_to_conversion_rate'] = $metrics['unique_contacts'] > 0
            ? round(($metrics['conversions'] / $metrics['unique_contacts']) * 100, 1)
            : 0.0;
        $metrics['conversion_rate'] = $metrics['visitors'] > 0
            ? round(($metrics['conversions'] / $metrics['visitors']) * 100, 1)
            : 0.0;
        $conversionBreakdown = (clone $conversions)
            ->selectRaw('conversion_type, COUNT(*) as total, COALESCE(SUM(amount_cents), 0) as revenue_cents')
            ->groupBy('conversion_type')
            ->orderByDesc('total')
            ->get();

        $bySource = (clone $events)
            ->where('event_name', 'page_viewed')
            ->selectRaw("COALESCE(utm_source, 'direct') as source, COUNT(*) as total")
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $byPage = (clone $events)
            ->where('event_name', 'page_viewed')
            ->selectRaw('offer_journey_page_id, COUNT(*) as total')
            ->groupBy('offer_journey_page_id')
            ->with('page')
            ->orderByDesc('total')
            ->get();

        $stepPerformance = $journey->pages()->orderBy('position')->get()->map(function ($page) use ($events) {
            $views = (clone $events)->where('offer_journey_page_id', $page->id)->where('event_name', 'page_viewed')->count();
            $actions = (clone $events)->where('offer_journey_page_id', $page->id)->whereIn('event_name', ['primary_cta_clicked', 'lead_captured'])->count();

            return [
                'page' => $page,
                'views' => $views,
                'actions' => $actions,
                'drop_off_rate' => $views > 0 ? round((max(0, $views - $actions) / $views) * 100, 1) : 0.0,
            ];
        });

        $byCampaign = (clone $events)
            ->whereNotNull('offer_journey_campaign_link_id')
            ->selectRaw("offer_journey_campaign_link_id, SUM(CASE WHEN event_name = 'page_viewed' THEN 1 ELSE 0 END) as views, SUM(CASE WHEN event_name = 'lead_captured' THEN 1 ELSE 0 END) as leads")
            ->groupBy('offer_journey_campaign_link_id')
            ->with('campaignLink')
            ->orderByDesc('views')
            ->get();

        $recommendations = collect();
        if ($metrics['visitors'] === 0) {
            $recommendations->push(['title' => 'Commencez par diffuser la page', 'body' => 'Créez un lien distinct pour le premier canal utilisé afin de mesurer son efficacité.', 'route' => route('offer-journeys.share', $journey), 'label' => 'Préparer le partage']);
        } elseif ($metrics['visitors'] >= 20 && $metrics['leads'] === 0) {
            $recommendations->push(['title' => 'Les visites ne deviennent pas encore des demandes', 'body' => 'Relisez le titre, la promesse, le nombre de champs et le texte du bouton. Modifiez un seul élément à la fois.', 'route' => route('offer-journeys.show', $journey), 'label' => 'Vérifier la page']);
        } elseif ($metrics['leads'] > 0 && $metrics['conversions'] === 0) {
            $recommendations->push(['title' => 'Des personnes sont intéressées, sans action confirmée', 'body' => 'Consultez leurs demandes et définissez une prochaine action avant de modifier la page.', 'route' => route('offer-journeys.contacts.index', ['journey_id' => $journey->id]), 'label' => 'Voir les personnes']);
        } else {
            $recommendations->push(['title' => 'Identifiez le canal le plus utile', 'body' => 'Comparez les liens de campagne et continuez à mesurer avant de changer la page.', 'route' => route('offer-journeys.share', $journey), 'label' => 'Voir les liens']);
        }

        return view('offer-journeys.practitioner.analytics', compact('journey', 'days', 'metrics', 'conversionBreakdown', 'bySource', 'byPage', 'stepPerformance', 'byCampaign', 'recommendations') + [
            'workspace' => $workspace->for($journey, $days),
            'workspaceSection' => 'analytics',
        ]);
    }
}
