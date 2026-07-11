<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class OfferJourneyAnalyticsController extends Controller
{
    use AuthorizesRequests;

    public function show(Request $request, OfferJourney $journey): View
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

        $metrics = [
            'views' => (clone $events)->where('event_name', 'page_viewed')->count(),
            'visitors' => (clone $events)->where('event_name', 'page_viewed')->whereNotNull('session_id')->distinct('session_id')->count('session_id'),
            'leads' => (clone $events)->where('event_name', 'lead_captured')->count(),
            'cta_clicks' => (clone $events)->where('event_name', 'primary_cta_clicked')->count(),
            'conversions' => $journey->conversions()->where('status', 'confirmed')->where('occurred_at', '>=', $from)->count(),
            'revenue_cents' => (int) $journey->conversions()->where('status', 'confirmed')->where('occurred_at', '>=', $from)->sum('amount_cents'),
        ];
        $metrics['conversion_rate'] = $metrics['visitors'] > 0
            ? round(($metrics['conversions'] / $metrics['visitors']) * 100, 1)
            : 0.0;

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

        return view('offer-journeys.practitioner.analytics', compact('journey', 'days', 'metrics', 'bySource', 'byPage', 'stepPerformance', 'byCampaign'));
    }
}
