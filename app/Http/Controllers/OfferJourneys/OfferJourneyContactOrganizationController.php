<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Domain\OfferJourneys\Services\OfferJourneySegmentQuery;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OfferJourneyContactOrganizationController extends Controller
{
    use AuthorizesRequests;

    public function segments(Request $request, OfferJourneySegmentQuery $segmentQuery): View
    {
        $this->authorize('viewAny', OfferJourneyContact::class);
        $segments = OfferJourneySegment::query()->where('user_id', $request->user()->id)->with('rules')->latest()->get();
        $segments->each(function ($segment) use ($request, $segmentQuery) {
            $segment->contacts_count = $segmentQuery->apply(
                OfferJourneyContact::query()->where('user_id', $request->user()->id),
                $segment
            )->count();
        });

        return view('offer-journeys.practitioner.contacts.segments', [
            'segments' => $segments,
            'tags' => OfferJourneyTag::query()->where('user_id', $request->user()->id)->orderBy('name')->get(),
            'journeys' => OfferJourney::query()->ownedBy($request->user())->orderBy('name')->get(),
        ]);
    }

    public function storeSegment(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', OfferJourneyContact::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'match_type' => ['nullable', Rule::in(['all', 'any'])],
            'rules' => ['nullable', 'array', 'min:1', 'max:10'],
            'rules.*.field' => ['required_with:rules', Rule::in(['status', 'tag', 'journey', 'inactive_days', 'marketing_consent'])],
            'rules.*.operator' => ['nullable', Rule::in(['equals', 'not_equals', 'has', 'missing', 'older_than_days'])],
            'rules.*.value' => ['nullable', 'string', 'max:180'],
            // Backward compatibility for the original one-rule form.
            'field' => ['nullable', Rule::in(['status', 'tag', 'journey', 'inactive_days', 'marketing_consent'])],
            'value' => ['nullable', 'string', 'max:180'],
        ]);

        $rules = collect($validated['rules'] ?? [
            ['field' => $validated['field'] ?? null, 'value' => $validated['value'] ?? null],
        ])->filter(fn (array $rule) => filled($rule['field'] ?? null))->values();

        if ($rules->isEmpty()) {
            return back()->withErrors(['rules' => 'Ajoutez au moins une règle au segment.'])->withInput();
        }

        foreach ($rules as $position => $rule) {
            $field = $rule['field'];
            $value = $rule['value'] ?? null;
            $operator = $this->normalizedOperator($field, $rule['operator'] ?? null);

            if ($field === 'tag') {
                abort_unless(
                    OfferJourneyTag::query()->where('user_id', $request->user()->id)->whereKey((int) $value)->exists(),
                    422,
                    'Étiquette invalide.'
                );
            }
            if ($field === 'journey') {
                abort_unless(
                    OfferJourney::query()->where('user_id', $request->user()->id)->whereKey((int) $value)->exists(),
                    422,
                    'Parcours invalide.'
                );
            }
            if ($field === 'inactive_days' && ((int) $value < 1 || (int) $value > 3650)) {
                return back()->withErrors(["rules.$position.value" => 'Le nombre de jours doit être compris entre 1 et 3650.'])->withInput();
            }

            $rules[$position] = compact('field', 'operator', 'value');
        }

        DB::transaction(function () use ($request, $validated, $rules) {
            $segment = OfferJourneySegment::query()->create([
                'user_id' => $request->user()->id,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'match_type' => $validated['match_type'] ?? 'all',
                'is_active' => true,
            ]);

            foreach ($rules as $position => $rule) {
                $segment->rules()->create([
                    'field' => $rule['field'],
                    'operator' => $rule['operator'],
                    'value_json' => ['value' => $rule['value'] ?? true],
                    'position' => $position,
                ]);
            }
        });

        return back()->with('success', 'Le segment a été créé.');
    }

    public function destroySegment(Request $request, OfferJourneySegment $segment): RedirectResponse
    {
        $this->authorize('viewAny', OfferJourneyContact::class);
        abort_unless((int) $segment->user_id === (int) $request->user()->id, 404);
        $segment->delete();

        return back()->with('success', 'Le segment a été supprimé.');
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', OfferJourneyContact::class);
        $validated = $request->validate(['name' => ['required', 'string', 'max:80']]);
        $slug = Str::slug($validated['name']) ?: 'etiquette';
        OfferJourneyTag::query()->firstOrCreate(
            ['user_id' => $request->user()->id, 'slug' => $slug],
            ['name' => $validated['name'], 'color' => 'olive', 'is_system' => false]
        );

        return back()->with('success', 'L’étiquette est disponible.');
    }

    public function attachTag(Request $request, OfferJourneyContact $contact): RedirectResponse
    {
        $this->authorize('update', $contact);
        $validated = $request->validate(['tag_id' => ['required', 'integer']]);
        $tag = OfferJourneyTag::query()->whereKey($validated['tag_id'])->where('user_id', $request->user()->id)->firstOrFail();
        $contact->tags()->syncWithoutDetaching([$tag->id]);
        $contact->activities()->create(['type' => 'tag_added', 'title' => 'Étiquette ajoutée : '.$tag->name, 'occurred_at' => now()]);

        return back()->with('success', 'L’étiquette a été ajoutée.');
    }

    public function detachTag(Request $request, OfferJourneyContact $contact, OfferJourneyTag $tag): RedirectResponse
    {
        $this->authorize('update', $contact);
        abort_unless((int) $tag->user_id === (int) $request->user()->id, 404);
        $contact->tags()->detach($tag->id);

        return back()->with('success', 'L’étiquette a été retirée.');
    }

    public function anonymize(Request $request, OfferJourneyContact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);
        $email = Str::lower(trim((string) $contact->email));

        DB::transaction(function () use ($request, $contact, $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                OfferJourneySuppression::query()->updateOrCreate(
                    ['user_id' => $request->user()->id, 'email_normalized' => $email, 'type' => 'erasure'],
                    ['offer_journey_contact_id' => $contact->id, 'reason' => 'anonymized_by_practitioner', 'source' => 'contact_record', 'suppressed_at' => now()]
                );
            }
            $contact->consents()->where('status', 'granted')->update(['status' => 'withdrawn', 'withdrawn_at' => now()]);
            $contact->tags()->detach();
            $contact->tasks()->where('status', 'open')->update(['status' => 'cancelled']);
            $contact->update([
                'email' => null, 'email_normalized' => null, 'first_name' => null, 'last_name' => null,
                'phone' => null, 'phone_normalized' => null, 'city' => null, 'postal_code' => null,
                'metadata' => null, 'status' => 'anonymized',
            ]);
            $contact->delete();
        });

        return redirect()->route('offer-journeys.contacts.index')->with('success', 'Le contact a été anonymisé. Les données métier existantes n’ont pas été modifiées.');
    }

    private function normalizedOperator(string $field, ?string $operator): string
    {
        return match ($field) {
            'tag' => in_array($operator, ['has', 'missing'], true) ? $operator : 'has',
            'status' => $operator === 'not_equals' ? 'not_equals' : 'equals',
            'inactive_days' => 'older_than_days',
            'journey', 'marketing_consent' => 'has',
            default => 'equals',
        };
    }
}
