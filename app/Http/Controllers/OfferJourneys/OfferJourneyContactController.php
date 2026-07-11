<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Domain\OfferJourneys\Services\OfferJourneySegmentQuery;
use App\Domain\OfferJourneys\Models\OfferJourneyTask;
use App\Domain\OfferJourneys\Models\OfferJourneySavedFilter;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Models\Appointment;
use App\Models\Reservation;
use App\Models\GiftVoucherOrder;
use App\Models\DigitalTrainingEnrollment;

class OfferJourneyContactController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request, OfferJourneySegmentQuery $segmentQuery): View
    {
        $this->authorize('viewAny', OfferJourneyContact::class);

        $query = OfferJourneyContact::query()
            ->ownedBy($request->user())
            ->with(['pipelineStage', 'tags'])
            ->withCount('entries')
            ->when($request->filled('journey_id'), fn ($query) => $query->whereHas('entries', fn ($entries) => $entries->where('offer_journey_id', (int) $request->input('journey_id'))))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('tag_id'), fn ($query) => $query->whereHas('tags', fn ($tags) => $tags->whereKey((int) $request->input('tag_id'))))
            ->when($request->filled('inactive_days'), fn ($query) => $query->where('last_activity_at', '<=', now()->subDays(max(1, (int) $request->input('inactive_days')))))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.str_replace(['%', '_'], ['\%', '\_'], (string) $request->string('q')).'%';
                $query->where(fn ($inner) => $inner
                    ->where('email', 'like', $term)
                    ->orWhere('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term));
            });

        $segment = null;
        if ($request->filled('segment_id')) {
            $segment = OfferJourneySegment::query()
                ->whereKey((int) $request->input('segment_id'))
                ->where('user_id', $request->user()->id)
                ->with('rules')
                ->first();
            if ($segment) {
                $segmentQuery->apply($query, $segment);
            }
        }

        $contacts = $query
            ->orderByDesc('last_activity_at')
            ->paginate(25)
            ->withQueryString();

        return view('offer-journeys.practitioner.contacts.index', [
            'contacts' => $contacts,
            'tags' => OfferJourneyTag::query()->where('user_id', $request->user()->id)->orderBy('name')->get(),
            'segments' => OfferJourneySegment::query()->where('user_id', $request->user()->id)->where('is_active', true)->orderBy('name')->get(),
            'dueTasks' => OfferJourneyTask::query()->where('user_id', $request->user()->id)->where('status', 'open')
                ->whereNotNull('due_at')->where('due_at', '<=', now()->addDays(7))->with('contact')->orderBy('due_at')->limit(10)->get(),
            'commercialToolsEnabled' => (bool) config('offer_journeys.commercial_tools_enabled', false),
            'contactImportEnabled' => (bool) config('offer_journeys.contact_import_enabled', false),
            'savedFilters' => OfferJourneySavedFilter::query()->where('user_id', $request->user()->id)->orderBy('name')->get(),
            'pipelineStages' => OfferJourneyPipelineStage::query()->where('user_id', $request->user()->id)->orderBy('position')->get(),
            'journeys' => OfferJourney::query()->ownedBy($request->user())->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(OfferJourneyContact $contact): View
    {
        $this->authorize('view', $contact);
        $contact->load([
            'pipelineStage',
            'tags',
            'entries.journey',
            'consents',
            'tasks' => fn ($query) => $query->latest('due_at'),
            'activities',
            'messageDeliveries' => fn ($query) => $query->latest()->limit(25),
        ]);

        $stages = \App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage::query()
            ->where('user_id', $contact->user_id)
            ->orderBy('position')
            ->get();

        $availableTags = OfferJourneyTag::query()->where('user_id', $contact->user_id)->orderBy('name')->get();

        $duplicateSuggestions = collect();
        $relatedBusiness = collect();
        $nextActionRecommendation = null;
        if (config('offer_journeys.commercial_tools_enabled', false)) {
            $duplicateSuggestions = OfferJourneyContact::query()
                ->where('user_id', $contact->user_id)
                ->whereKeyNot($contact->id)
                ->where(function ($query) use ($contact) {
                    if ($contact->phone_normalized) {
                        $query->orWhere('phone_normalized', $contact->phone_normalized);
                    }
                    if ($contact->first_name && $contact->last_name) {
                        $query->orWhere(fn ($names) => $names->where('first_name', $contact->first_name)->where('last_name', $contact->last_name));
                    }
                })
                ->limit(5)
                ->get();

            if ($contact->client_profile_id) {
                Appointment::query()->where('user_id', $contact->user_id)->where('client_profile_id', $contact->client_profile_id)
                    ->latest('appointment_date')->limit(20)->get(['id', 'appointment_date', 'status', 'type'])
                    ->each(fn ($item) => $relatedBusiness->push(['type' => 'Rendez-vous', 'label' => $item->type ?: 'Rendez-vous', 'status' => $item->status, 'date' => $item->appointment_date]));
            }
            Reservation::query()->whereRaw('LOWER(email) = ?', [$contact->email_normalized])
                ->whereHas('event', fn ($query) => $query->where('user_id', $contact->user_id))->with('event:id,name,user_id')
                ->latest()->limit(20)->get()->each(fn ($item) => $relatedBusiness->push(['type' => 'Événement', 'label' => $item->event?->name ?: 'Inscription', 'status' => $item->status, 'date' => $item->created_at]));
            GiftVoucherOrder::query()->where('user_id', $contact->user_id)->whereRaw('LOWER(buyer_email) = ?', [$contact->email_normalized])
                ->latest()->limit(20)->get(['id', 'status', 'amount_cents', 'created_at'])->each(fn ($item) => $relatedBusiness->push(['type' => 'Bon cadeau', 'label' => number_format($item->amount_cents / 100, 2, ',', ' ').' €', 'status' => $item->status, 'date' => $item->created_at]));
            DigitalTrainingEnrollment::query()->whereRaw('LOWER(participant_email) = ?', [$contact->email_normalized])
                ->whereHas('training', fn ($query) => $query->where('user_id', $contact->user_id))->with('training:id,title,user_id')
                ->latest()->limit(20)->get()->each(fn ($item) => $relatedBusiness->push(['type' => 'Formation', 'label' => $item->training?->title ?: 'Formation', 'status' => $item->completed_at ? 'completed' : 'active', 'date' => $item->created_at]));

            $nextActionRecommendation = $contact->tasks->firstWhere('status', 'open')?->title
                ?? match ($contact->status) {
                    'new' => 'Qualifier la demande et choisir une prochaine action.',
                    'qualifying' => 'Proposer un échange ou un créneau adapté.',
                    'contacted' => 'Planifier un rappel avec une date précise.',
                    'not_now' => 'Définir une date de reprise si la personne l’a demandée.',
                    default => null,
                };
        }

        return view('offer-journeys.practitioner.contacts.show', compact('contact', 'stages', 'availableTags', 'duplicateSuggestions', 'relatedBusiness', 'nextActionRecommendation'));
    }

    public function updateStatus(Request $request, OfferJourneyContact $contact): RedirectResponse
    {
        $this->authorize('update', $contact);
        $validated = $request->validate([
            'status' => ['required', Rule::in(['new', 'qualifying', 'contacted', 'converted', 'not_now'])],
        ]);
        $contact->update(['status' => $validated['status'], 'last_activity_at' => now()]);

        return back()->with('success', 'Le statut du contact a été mis à jour.');
    }

    public function storeNote(Request $request, OfferJourneyContact $contact): RedirectResponse
    {
        $this->authorize('update', $contact);
        $validated = $request->validate(['note' => ['required', 'string', 'max:2000']]);
        $contact->activities()->create([
            'type' => 'internal_note',
            'title' => 'Note interne',
            'metadata' => ['note' => $validated['note']],
            'occurred_at' => now(),
        ]);

        return back()->with('success', 'La note a été ajoutée.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', OfferJourneyContact::class);
        $user = $request->user();

        return Response::streamDownload(function () use ($user) {
            $handle = fopen('php://output', 'wb');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Prénom', 'Nom', 'Email', 'Téléphone', 'Statut', 'Dernière activité'], ';');

            OfferJourneyContact::query()->ownedBy($user)->orderBy('id')->chunkById(250, function ($contacts) use ($handle) {
                foreach ($contacts as $contact) {
                    fputcsv($handle, [
                        $contact->first_name,
                        $contact->last_name,
                        $contact->email,
                        $contact->phone,
                        $contact->status,
                        $contact->last_activity_at?->format('d/m/Y H:i'),
                    ], ';');
                }
            });

            fclose($handle);
        }, 'contacts-interesses-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
