<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Audience;
use App\Models\ClientProfile;
use App\Models\Newsletter;
use App\Models\NewsletterMonthlyUsage;
use App\Models\NewsletterOptOut;
use App\Models\NewsletterRecipient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MobileNewsletterController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $canUseNewsletters = $this->canUseNewsletters($user);
        $monthKey = $this->newsletterMonthKey();
        $quotaLimit = $this->newsletterMonthlyQuotaFor($user);
        $quotaUsed = $this->newsletterUsageFor($user, $monthKey);

        $newsletters = Newsletter::query()
            ->with('audience')
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return view('mobile.newsletters.index', [
            'newsletters' => $newsletters,
            'canUseNewsletters' => $canUseNewsletters,
            'monthKey' => $monthKey,
            'quotaLimit' => $quotaLimit,
            'quotaUsed' => $quotaUsed,
            'quotaPercent' => $quotaLimit > 0 ? min(100, (int) round(($quotaUsed / $quotaLimit) * 100)) : 0,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $this->authorizeNewsletterFeature($user);

        $newsletter = new Newsletter([
            'from_name' => $user->name ?? $user->company_name ?? config('app.name'),
            'from_email' => 'contact@aromamade.com',
            'background_color' => '#ffffff',
        ]);

        return view('mobile.newsletters.form', [
            'title' => 'Nouvelle newsletter',
            'newsletter' => $newsletter,
            'audiences' => $this->ownedAudiences(),
            'mobileFields' => $this->mobileFieldsFromNewsletter($newsletter),
            'action' => route('mobile.newsletters.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $this->authorizeNewsletterFeature($user);

        $validated = $this->validatedPayload($request);

        $newsletter = Newsletter::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'subject' => $validated['subject'],
            'preheader' => $validated['preheader'] ?? null,
            'from_name' => $validated['from_name'],
            'from_email' => 'contact@aromamade.com',
            'background_color' => $validated['background_color'],
            'content_json' => json_encode($this->blocksFromPayload($validated)),
            'status' => 'draft',
            'recipients_count' => 0,
            'audience_id' => $validated['audience_id'] ?? null,
        ]);

        return redirect()
            ->route('mobile.newsletters.show', $newsletter)
            ->with('success', 'Newsletter creee.');
    }

    public function show(Newsletter $newsletter)
    {
        $this->ensureOwnsNewsletter($newsletter);
        $this->authorizeNewsletterFeature(Auth::user());

        $newsletter->load(['audience', 'recipients.clientProfile']);

        return view('mobile.newsletters.show', [
            'newsletter' => $newsletter,
            'blocks' => $newsletter->blocks,
            'targetCount' => $newsletter->status === 'sent'
                ? (int) $newsletter->recipients_count
                : $this->targetClients($newsletter)->count(),
            'quotaLimit' => $this->newsletterMonthlyQuotaFor(Auth::user()),
            'quotaUsed' => $this->newsletterUsageFor(Auth::user(), $this->newsletterMonthKey()),
        ]);
    }

    public function edit(Newsletter $newsletter)
    {
        $this->ensureOwnsNewsletter($newsletter);
        $this->authorizeNewsletterFeature(Auth::user());

        $newsletter->load('audience');

        return view('mobile.newsletters.form', [
            'title' => 'Modifier la newsletter',
            'newsletter' => $newsletter,
            'audiences' => $this->ownedAudiences(),
            'mobileFields' => $this->mobileFieldsFromNewsletter($newsletter),
            'action' => route('mobile.newsletters.update', $newsletter),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, Newsletter $newsletter)
    {
        $this->ensureOwnsNewsletter($newsletter);
        $this->authorizeNewsletterFeature(Auth::user());

        $validated = $this->validatedPayload($request);

        $newsletter->update([
            'title' => $validated['title'],
            'subject' => $validated['subject'],
            'preheader' => $validated['preheader'] ?? null,
            'from_name' => $validated['from_name'],
            'from_email' => 'contact@aromamade.com',
            'background_color' => $validated['background_color'],
            'content_json' => json_encode($this->blocksFromPayload($validated)),
            'audience_id' => $validated['audience_id'] ?? null,
        ]);

        return redirect()
            ->route('mobile.newsletters.show', $newsletter)
            ->with('success', 'Newsletter mise a jour.');
    }

    public function destroy(Newsletter $newsletter)
    {
        $this->ensureOwnsNewsletter($newsletter);
        $this->authorizeNewsletterFeature(Auth::user());

        $newsletter->delete();

        return redirect()
            ->route('mobile.newsletters.index')
            ->with('success', 'Newsletter supprimee.');
    }

    public function sendTest(Request $request, Newsletter $newsletter)
    {
        $this->ensureOwnsNewsletter($newsletter);
        $this->authorizeNewsletterFeature(Auth::user());

        $validated = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        $client = (object) [
            'first_name' => 'Prenom',
            'last_name' => 'Nom',
        ];

        Mail::send('emails.newsletter', [
            'newsletter' => $newsletter,
            'client' => $client,
            'unsubscribeUrl' => null,
        ], function ($message) use ($newsletter, $validated) {
            $user = $newsletter->user;

            $message->to($validated['test_email'])
                ->from($newsletter->from_email, $newsletter->from_name)
                ->replyTo(
                    $user?->email ?? config('mail.from.address'),
                    $user?->name ?? config('mail.from.name')
                )
                ->subject('[TEST] ' . $newsletter->subject);
        });

        return redirect()
            ->route('mobile.newsletters.show', $newsletter)
            ->with('success', 'Email de test envoye.');
    }

    public function sendNow(Newsletter $newsletter)
    {
        $this->ensureOwnsNewsletter($newsletter);
        $this->authorizeNewsletterFeature(Auth::user());

        if ($newsletter->status === 'sent') {
            return redirect()
                ->route('mobile.newsletters.show', $newsletter)
                ->with('error', 'Cette newsletter est deja marquee comme envoyee.');
        }

        $user = Auth::user();
        $clients = $this->targetClients($newsletter);

        if ($clients->isEmpty()) {
            return redirect()
                ->route('mobile.newsletters.show', $newsletter)
                ->with('error', 'Aucun client avec email disponible pour cet envoi.');
        }

        $this->assertNewsletterQuota($user, $clients->count());

        $newsletter->recipients()->delete();

        foreach ($clients as $client) {
            $recipient = NewsletterRecipient::create([
                'newsletter_id' => $newsletter->id,
                'client_profile_id' => $client->id,
                'email' => $client->email,
                'status' => 'pending',
                'unsubscribe_token' => Str::uuid()->toString(),
            ]);

            $this->sendNewsletterEmail($newsletter, $client, $recipient);
        }

        $this->incrementNewsletterUsage($user, $clients->count());

        $newsletter->update([
            'status' => 'sent',
            'sent_at' => now(),
            'recipients_count' => $newsletter->recipients()->count(),
        ]);

        return redirect()
            ->route('mobile.newsletters.show', $newsletter)
            ->with('success', 'Newsletter envoyee a ' . $newsletter->recipients_count . ' destinataires.');
    }

    private function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'background_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'audience_id' => ['nullable', 'integer', 'exists:audiences,id'],
            'heading' => ['nullable', 'string', 'max:255'],
            'body_text' => ['required', 'string'],
            'image_url' => ['nullable', 'url', 'max:2048'],
            'image_alt' => ['nullable', 'string', 'max:255'],
            'button_label' => ['nullable', 'string', 'max:80'],
            'button_url' => ['nullable', 'url', 'max:2048'],
            'include_divider' => ['nullable', 'boolean'],
        ]);

        $audienceId = $validated['audience_id'] ?? null;
        if ($audienceId) {
            $owned = Audience::query()
                ->where('user_id', Auth::id())
                ->whereKey($audienceId)
                ->exists();

            if (!$owned) {
                throw ValidationException::withMessages([
                    'audience_id' => 'Cette audience ne vous appartient pas.',
                ]);
            }
        }

        $validated['background_color'] = $validated['background_color'] ?? '#ffffff';
        $validated['include_divider'] = (bool) ($validated['include_divider'] ?? false);

        return $validated;
    }

    private function blocksFromPayload(array $payload): array
    {
        $blocks = [
            [
                'type' => 'heading_text',
                'heading' => $payload['heading'] ?: $payload['subject'],
                'text' => $payload['body_text'],
                'html' => $this->plainTextToHtml($payload['body_text']),
                'heading_size' => '22px',
                'heading_color' => '#111111',
                'text_size' => '14px',
                'text_color' => '#333333',
                'font_family' => 'Montserrat',
                'text_align' => 'left',
            ],
        ];

        if (!empty($payload['image_url'])) {
            $blocks[] = [
                'type' => 'image',
                'url' => $payload['image_url'],
                'alt' => $payload['image_alt'] ?? '',
            ];
        }

        if ($payload['include_divider']) {
            $blocks[] = [
                'type' => 'divider',
            ];
        }

        if (!empty($payload['button_url'])) {
            $blocks[] = [
                'type' => 'button',
                'label' => $payload['button_label'] ?: 'En savoir plus',
                'url' => $payload['button_url'],
                'font_size' => '14px',
                'text_color' => '#ffffff',
                'background_color' => '#647a0b',
            ];
        }

        return $blocks;
    }

    private function mobileFieldsFromNewsletter(Newsletter $newsletter): array
    {
        $blocks = $newsletter->blocks;

        $headingBlock = collect($blocks)->firstWhere('type', 'heading_text');
        $textBlock = collect($blocks)->firstWhere('type', 'text');
        $imageBlock = collect($blocks)->firstWhere('type', 'image');
        $buttonBlock = collect($blocks)->firstWhere('type', 'button');

        $bodyHtml = $headingBlock['html'] ?? $textBlock['html'] ?? '';
        $bodyText = $this->htmlToPlainText($bodyHtml);

        return [
            'heading' => $headingBlock['heading'] ?? '',
            'body_text' => $bodyText ?: $this->htmlToPlainText($headingBlock['text'] ?? $textBlock['text'] ?? ''),
            'image_url' => $imageBlock['url'] ?? '',
            'image_alt' => $imageBlock['alt'] ?? '',
            'button_label' => $buttonBlock['label'] ?? '',
            'button_url' => $buttonBlock['url'] ?? '',
            'include_divider' => collect($blocks)->contains(fn ($block) => ($block['type'] ?? null) === 'divider'),
        ];
    }

    private function targetClients(Newsletter $newsletter): Collection
    {
        $user = Auth::user();
        $optedOutEmails = NewsletterOptOut::query()
            ->where('user_id', $user->id)
            ->pluck('email')
            ->map(fn ($email) => strtolower($email))
            ->all();

        $query = ClientProfile::query()
            ->where('user_id', $user->id)
            ->whereNotNull('email');

        if ($newsletter->audience_id) {
            $audience = Audience::query()
                ->where('user_id', $user->id)
                ->whereKey($newsletter->audience_id)
                ->firstOrFail();

            $query->whereIn('id', $audience->clients()->pluck('client_profiles.id'));
        }

        return $query->get()
            ->filter(fn (ClientProfile $client) => !in_array(strtolower($client->email), $optedOutEmails, true))
            ->values();
    }

    private function sendNewsletterEmail(Newsletter $newsletter, ClientProfile $client, NewsletterRecipient $recipient): void
    {
        $unsubscribeUrl = route('unsubscribe.newsletter', [
            'token' => $recipient->unsubscribe_token,
        ]);

        Mail::send('emails.newsletter', [
            'newsletter' => $newsletter,
            'client' => $client,
            'unsubscribeUrl' => $unsubscribeUrl,
        ], function ($message) use ($newsletter, $recipient) {
            $user = $newsletter->user;

            $message->to($recipient->email)
                ->from($newsletter->from_email, $newsletter->from_name)
                ->replyTo(
                    $user?->email ?? config('mail.from.address'),
                    $user?->name ?? config('mail.from.name')
                )
                ->subject($newsletter->subject);
        });

        $recipient->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    private function ownedAudiences()
    {
        return Audience::query()
            ->where('user_id', Auth::id())
            ->withCount('clients')
            ->orderBy('name')
            ->get();
    }

    private function ensureOwnsNewsletter(Newsletter $newsletter): void
    {
        if ((int) $newsletter->user_id !== (int) Auth::id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function authorizeNewsletterFeature(?User $user): void
    {
        abort_unless($this->canUseNewsletters($user), 403);
    }

    private function canUseNewsletters(?User $user): bool
    {
        return $user?->canUseFeature('newsletter') ?? false;
    }

    private function newsletterMonthKey(): string
    {
        return now()->format('Y-m');
    }

    private function newsletterMonthlyQuotaFor(?User $user): int
    {
        return (int) config('newsletters.monthly_quota', 1000);
    }

    private function newsletterUsageFor(?User $user, string $month): int
    {
        if (!$user) {
            return 0;
        }

        return (int) NewsletterMonthlyUsage::query()
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->value('sent_count');
    }

    private function assertNewsletterQuota(User $user, int $toSend): void
    {
        $month = $this->newsletterMonthKey();
        $quota = $this->newsletterMonthlyQuotaFor($user);

        $usage = NewsletterMonthlyUsage::firstOrCreate(
            ['user_id' => $user->id, 'month' => $month],
            ['sent_count' => 0]
        );

        if (($usage->sent_count + $toSend) > $quota) {
            abort(429, "Quota newsletters depasse pour {$month}.");
        }
    }

    private function incrementNewsletterUsage(User $user, int $sent): void
    {
        $usage = NewsletterMonthlyUsage::firstOrCreate(
            ['user_id' => $user->id, 'month' => $this->newsletterMonthKey()],
            ['sent_count' => 0]
        );

        $usage->increment('sent_count', $sent);
    }

    private function plainTextToHtml(string $text): string
    {
        return nl2br(e($text), false);
    }

    private function htmlToPlainText(?string $html): string
    {
        $html = str_replace(['<br>', '<br/>', '<br />'], "\n", (string) $html);
        $html = preg_replace('/<\/(p|div|h1|h2|h3)>/i', "\n", $html) ?? $html;

        return trim(html_entity_decode(strip_tags($html)));
    }
}
