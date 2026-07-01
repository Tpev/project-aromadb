<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Mail\CommunityInviteMail;
use App\Models\ClientProfile;
use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use App\Services\CommunityMessageService;
use App\Support\UploadLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MobileCommunityController extends Controller
{
    public function __construct(private CommunityMessageService $messageService)
    {
    }

    public function index()
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        $communities = CommunityGroup::query()
            ->where('user_id', Auth::id())
            ->withCount([
                'channels',
                'messages',
                'activeMembers as active_members_count',
                'invitedMembers as invited_members_count',
            ])
            ->with(['channels.pinnedMessage'])
            ->latest('id')
            ->get();

        return view('mobile.communities.index', compact('communities'));
    }

    public function create()
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        return view('mobile.communities.form', [
            'title' => 'Nouvelle communaute',
            'community' => new CommunityGroup(['is_archived' => false]),
            'action' => route('mobile.communities.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        if ($redirect = $this->inactiveLicenseRedirect()) {
            return $redirect;
        }

        $payload = $this->validatedCommunityPayload($request);

        $community = CommunityGroup::create([
            'user_id' => Auth::id(),
            'name' => $payload['name'],
            'description' => $payload['description'],
            'is_archived' => false,
        ]);

        $community->channels()->createMany([
            [
                'name' => 'General',
                'channel_type' => CommunityChannel::TYPE_DISCUSSION,
                'position' => 1,
            ],
            [
                'name' => 'Annonces',
                'channel_type' => CommunityChannel::TYPE_ANNOUNCEMENTS,
                'position' => 2,
            ],
        ]);

        return redirect()
            ->route('mobile.communities.show', $community)
            ->with('success', 'Communaute creee.');
    }

    public function show(Request $request, CommunityGroup $community)
    {
        $this->ensureOwnsCommunity($community);

        $community->load([
            'channels.pinnedMessage.user',
            'channels.pinnedMessage.clientProfile',
            'channels.pinnedMessage.attachments',
            'members.clientProfile',
        ]);

        $selectedChannel = $community->channels
            ->firstWhere('id', (int) $request->integer('channel'))
            ?? $community->channels->first();

        $messages = collect();
        if ($selectedChannel) {
            $messages = $selectedChannel->messages()
                ->with(['user', 'clientProfile', 'attachments', 'channel'])
                ->latest()
                ->take(80)
                ->get()
                ->reverse()
                ->values();
        }

        return view('mobile.communities.show', [
            'community' => $community,
            'selectedChannel' => $selectedChannel,
            'messages' => $messages,
            'availableClients' => $this->availableClients(),
            'attachmentLimitLabel' => UploadLimit::communityAttachmentLimitLabel(),
        ]);
    }

    public function edit(CommunityGroup $community)
    {
        $this->ensureOwnsCommunity($community);

        return view('mobile.communities.form', [
            'title' => 'Modifier la communaute',
            'community' => $community,
            'action' => route('mobile.communities.update', $community),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, CommunityGroup $community)
    {
        $this->ensureOwnsCommunity($community);

        $payload = $this->validatedCommunityPayload($request, true);

        $community->update([
            'name' => $payload['name'],
            'description' => $payload['description'],
            'is_archived' => $request->boolean('is_archived'),
        ]);

        return redirect()
            ->route('mobile.communities.show', $community)
            ->with('success', 'Communaute mise a jour.');
    }

    public function destroy(CommunityGroup $community)
    {
        $this->ensureOwnsCommunity($community);

        $community->loadMissing('messages.attachments');
        foreach ($community->messages as $message) {
            foreach ($message->attachments as $attachment) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        }

        $community->delete();

        return redirect()
            ->route('mobile.communities.index')
            ->with('success', 'Communaute supprimee.');
    }

    public function storeMember(Request $request, CommunityGroup $community)
    {
        $this->ensureOwnsCommunity($community);

        $data = $request->validate([
            'client_profile_id' => [
                'required',
                'integer',
                Rule::exists('client_profiles', 'id')->where(fn ($query) => $query->where('user_id', Auth::id())),
            ],
        ]);

        $client = ClientProfile::query()
            ->where('user_id', Auth::id())
            ->findOrFail((int) $data['client_profile_id']);

        $member = CommunityMember::firstOrNew([
            'community_group_id' => $community->id,
            'client_profile_id' => $client->id,
        ]);

        if ($member->exists && $member->status === CommunityMember::STATUS_ACTIVE) {
            return redirect()
                ->route('mobile.communities.show', $community)
                ->with('success', 'Ce client fait deja partie de la communaute.');
        }

        $member->status = CommunityMember::STATUS_INVITED;
        $member->invited_at = now();
        $member->invitation_email_sent_at = $client->email ? now() : null;
        $member->joined_at = null;
        $member->removed_at = null;
        $member->save();

        if ($client->email && ! $this->sendInvitationEmail($community, $client)) {
            return redirect()
                ->route('mobile.communities.show', $community)
                ->with('error', 'Invitation enregistree, mais l email n a pas pu etre envoye.');
        }

        return redirect()
            ->route('mobile.communities.show', $community)
            ->with('success', $client->email ? 'Invitation envoyee.' : 'Invitation enregistree.');
    }

    public function destroyMember(CommunityGroup $community, CommunityMember $member)
    {
        $this->ensureOwnsCommunity($community);
        abort_unless((int) $member->community_group_id === (int) $community->id, 404);

        $member->update([
            'status' => CommunityMember::STATUS_REMOVED,
            'removed_at' => now(),
        ]);

        return redirect()
            ->route('mobile.communities.show', $community)
            ->with('success', 'Membre retire de la communaute.');
    }

    public function storeMessage(Request $request, CommunityGroup $community)
    {
        $this->ensureOwnsCommunity($community);

        if ($community->is_archived) {
            return back()->with('error', 'Cette communaute est archivee.');
        }

        $data = $request->validate([
            'community_channel_id' => [
                'required',
                'integer',
                Rule::exists('community_channels', 'id')->where(
                    fn ($query) => $query->where('community_group_id', $community->id)
                ),
            ],
            'content' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => [
                'file',
                'max:' . UploadLimit::communityAttachmentValidationMaxKilobytes(),
                'mimes:pdf,jpg,jpeg,png,webp,gif,doc,docx,xls,xlsx,ppt,pptx,txt,csv,mp3,m4a,wav,ogg',
            ],
        ]);

        $channel = CommunityChannel::query()
            ->where('community_group_id', $community->id)
            ->findOrFail((int) $data['community_channel_id']);

        $this->messageService->createPractitionerMessage(
            community: $community,
            channel: $channel,
            user: $request->user(),
            content: $data['content'],
            attachments: $request->file('attachments', []),
        );

        return redirect()
            ->route('mobile.communities.show', ['community' => $community->id, 'channel' => $channel->id])
            ->with('success', 'Message envoye.');
    }

    private function validatedCommunityPayload(Request $request, bool $includeArchived = false): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_archived' => [$includeArchived ? 'nullable' : 'exclude', 'boolean'],
        ]);

        return [
            'name' => trim($validated['name']),
            'description' => trim((string) ($validated['description'] ?? '')) ?: null,
        ];
    }

    private function availableClients()
    {
        return ClientProfile::query()
            ->where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    }

    private function sendInvitationEmail(CommunityGroup $community, ClientProfile $client): bool
    {
        try {
            Mail::to($client->email)->queue(new CommunityInviteMail(
                community: $community->fresh('user'),
                client: $client->fresh('user'),
                joinUrl: $this->buildJoinUrlFor($client),
                requiresAccountSetup: ! $client->hasEspaceClient(),
            ));

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function buildJoinUrlFor(ClientProfile $client): string
    {
        if ($client->hasEspaceClient()) {
            return route('client.communities.index');
        }

        $plainToken = Str::uuid()->toString();

        $client->forceFill([
            'password_setup_token_hash' => hash('sha256', $plainToken),
            'password_setup_expires_at' => now()->addDays(3),
        ])->save();

        return route('client.setup.show', [
            'token' => $plainToken,
            'redirect' => route('client.communities.index'),
        ]);
    }

    private function ensureOwnsCommunity(CommunityGroup $community): void
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);
    }

    private function inactiveLicenseRedirect()
    {
        return Auth::user()?->license_status === 'inactive'
            ? redirect('/license-tiers/pricing')
            : null;
    }
}
