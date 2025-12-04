<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\Audience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\NewsletterOptOut;

class NewsletterController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $newsletters = Newsletter::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('newsletters.index', compact('newsletters'));
    }

    public function create()
    {
        $user = Auth::user();

        $newsletter = new Newsletter([
            'from_name'  => $user->name ?? $user->company_name ?? config('app.name'),
            'from_email' => 'news@aromamade.com',
        ]);

        // On laisse Alpine gérer le bloc par défaut (JS)
        $initialBlocks = [];

        $audiences = Audience::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return view('newsletters.create', compact('newsletter', 'initialBlocks', 'audiences'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'subject'          => 'required|string|max:255',
            'preheader'        => 'nullable|string|max:255',
            'from_name'        => 'required|string|max:255',
            'from_email'       => 'required|email|max:255',
            'background_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'content_json'     => 'required|string',
            'audience_id'      => 'nullable|integer|exists:audiences,id',
        ]);

        $blocks = json_decode($data['content_json'], true) ?: [];

        if (empty($blocks)) {
            return back()
                ->withInput()
                ->withErrors(['blocks' => 'Ajoutez au moins un bloc à votre newsletter.']);
        }

        // Vérifier que l’audience appartient bien au thérapeute (si fournie)
        $audienceId = $data['audience_id'] ?? null;
        if ($audienceId) {
            $audience = Audience::where('user_id', $user->id)
                ->where('id', $audienceId)
                ->firstOrFail();
        }

        $newsletter = new Newsletter();
        $newsletter->user_id          = $user->id;
        $newsletter->title            = $data['title'];
        $newsletter->subject          = $data['subject'];
        $newsletter->preheader        = $data['preheader'] ?? null;
        $newsletter->from_name        = $data['from_name'];
        $newsletter->from_email       = $data['from_email'];
        $newsletter->background_color = $data['background_color'] ?? '#ffffff';
        $newsletter->content_json     = json_encode($blocks);
        $newsletter->status           = 'draft';
        $newsletter->recipients_count = 0;
        $newsletter->audience_id      = $audienceId ?? null;
        $newsletter->save();

        return redirect()
            ->route('newsletters.edit', $newsletter)
            ->with('success', 'Newsletter créée. Vous pouvez maintenant la prévisualiser et l’envoyer.');
    }

    public function edit(Newsletter $newsletter)
    {
        $this->authorizeNewsletter($newsletter);

        $user = Auth::user();

        // Blocks venant de la BDD (via accessor ->blocks)
        $initialBlocks = $newsletter->blocks;

        $audiences = Audience::where('user_id', $user->id)
            ->orderBy('name')
            ->get();

        return view('newsletters.edit', compact('newsletter', 'initialBlocks', 'audiences'));
    }

    public function update(Request $request, Newsletter $newsletter)
    {
        $this->authorizeNewsletter($newsletter);

        $user = Auth::user();

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'subject'          => 'required|string|max:255',
            'preheader'        => 'nullable|string|max:255',
            'from_name'        => 'required|string|max:255',
            'from_email'       => 'required|email|max:255',
            'background_color' => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/',
            'content_json'     => 'required|string',
            'audience_id'      => 'nullable|integer|exists:audiences,id',
        ]);

        $blocks = json_decode($data['content_json'], true) ?: [];

        if (empty($blocks)) {
            return back()
                ->withInput()
                ->withErrors(['blocks' => 'Ajoutez au moins un bloc à votre newsletter.']);
        }

        // Vérifier que l’audience appartient bien au thérapeute (si fournie)
        $audienceId = $data['audience_id'] ?? null;
        if ($audienceId) {
            $audience = Audience::where('user_id', $user->id)
                ->where('id', $audienceId)
                ->firstOrFail();
        }

        $newsletter->title            = $data['title'];
        $newsletter->subject          = $data['subject'];
        $newsletter->preheader        = $data['preheader'] ?? null;
        $newsletter->from_name        = $data['from_name'];
        $newsletter->from_email       = $data['from_email'];
        $newsletter->background_color = $data['background_color'] ?? '#ffffff';
        $newsletter->content_json     = json_encode($blocks);
        $newsletter->audience_id      = $audienceId ?? null;
        $newsletter->save();

        return redirect()
            ->route('newsletters.edit', $newsletter)
            ->with('success', 'Newsletter mise à jour.');
    }

    public function show(Newsletter $newsletter)
    {
        $this->authorizeNewsletter($newsletter);

        // Simple web preview, using dummy "client"
        $client = (object)[
            'first_name' => 'Prénom',
            'last_name'  => 'Nom',
        ];

        return view('newsletters.show', [
            'newsletter' => $newsletter,
            'client'     => $client,
        ]);
    }

    public function destroy(Newsletter $newsletter)
    {
        $this->authorizeNewsletter($newsletter);

        $newsletter->delete();

        return redirect()
            ->route('newsletters.index')
            ->with('success', 'Newsletter supprimée.');
    }

    public function sendTest(Request $request, Newsletter $newsletter)
    {
        $this->authorizeNewsletter($newsletter);

        $request->validate([
            'test_email' => 'required|email',
        ]);

        $to = $request->input('test_email');

        $client = (object)[
            'first_name' => 'Prénom',
            'last_name'  => 'Nom',
        ];

        Mail::send('emails.newsletter', [
            'newsletter'     => $newsletter,
            'client'         => $client,
            'unsubscribeUrl' => null,
        ], function ($message) use ($newsletter, $to) {
            $message->to($to)
                ->from($newsletter->from_email, $newsletter->from_name)
                ->subject('[TEST] ' . $newsletter->subject);
        });

        return back()->with('success', 'Email de test envoyé à ' . $to);
    }

public function sendNow(Newsletter $newsletter)
{
    $this->authorizeNewsletter($newsletter);

    if ($newsletter->status === 'sent') {
        return back()->with('error', 'Cette newsletter est déjà marquée comme envoyée.');
    }

    $user = Auth::user();

    // Emails désabonnés pour ce thérapeute
    $optedOutEmails = NewsletterOptOut::where('user_id', $user->id)
        ->pluck('email')
        ->map(fn ($e) => strtolower($e))
        ->toArray();

    // Tous les clients avec email
    $clients = ClientProfile::where('user_id', $user->id)
        ->whereNotNull('email')
        ->get()
        ->filter(function ($client) use ($optedOutEmails) {
            return !in_array(strtolower($client->email), $optedOutEmails, true);
        });

    if ($clients->isEmpty()) {
        return back()->with('error', 'Aucun client avec email disponible pour l’envoi (ou tous sont désabonnés).');
    }

    // On nettoie les destinataires pré-existants pour cette newsletter
    $newsletter->recipients()->delete();

    foreach ($clients as $client) {
        $recipient = new NewsletterRecipient();
        $recipient->newsletter_id     = $newsletter->id;
        $recipient->client_profile_id = $client->id;
        $recipient->email             = $client->email;
        $recipient->status            = 'pending';
        $recipient->unsubscribe_token = Str::uuid()->toString();
        $recipient->save();

        $this->sendNewsletterEmail($newsletter, $client, $recipient);
    }

    $newsletter->status            = 'sent';
    $newsletter->sent_at           = now();
    $newsletter->recipients_count  = $newsletter->recipients()->count();
    $newsletter->save();

    return redirect()
        ->route('newsletters.index')
        ->with('success', 'Newsletter envoyée à ' . $newsletter->recipients_count . ' destinataires.');
}


protected function sendNewsletterEmail(Newsletter $newsletter, $client, NewsletterRecipient $recipient): void
{
    $unsubscribeUrl = route('unsubscribe.newsletter', [
        'token' => $recipient->unsubscribe_token,
    ]);

    Mail::send('emails.newsletter', [
        'newsletter'     => $newsletter,
        'client'         => $client,
        'unsubscribeUrl' => $unsubscribeUrl,
    ], function ($message) use ($newsletter, $recipient) {
        $message->to($recipient->email)
            ->from($newsletter->from_email, $newsletter->from_name)
            ->subject($newsletter->subject);
    });

    $recipient->status  = 'sent';
    $recipient->sent_at = now();
    $recipient->save();
}


    protected function authorizeNewsletter(Newsletter $newsletter): void
    {
        $user = Auth::user();

        if ($newsletter->user_id !== $user->id) {
            abort(403);
        }
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:4096', // 4 Mo
        ]);

        $user = Auth::user();

        $path = $request->file('image')->store(
            'newsletters/' . $user->id,
            'public'
        );

        $url = asset('storage/' . $path);

        return response()->json([
            'url' => $url,
        ]);
    }
}
