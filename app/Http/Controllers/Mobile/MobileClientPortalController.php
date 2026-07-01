<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Mail\ClientFileUploadedTherapistMail;
use App\Mail\ClientMessageReceivedTherapistMail;
use App\Models\ClientFile;
use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use App\Models\CommunityMessage;
use App\Models\Message;
use App\Notifications\CommunityInviteAccepted;
use App\Notifications\CommunityMessagePosted;
use App\Services\CommunityMessageService;
use App\Support\UploadLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MobileClientPortalController extends Controller
{
    public function __construct(protected CommunityMessageService $messageService)
    {
    }

    public function showLogin(): View
    {
        return view('mobile.client.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('client')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('mobile.client.home'));
        }

        return back()
            ->withErrors(['email' => 'Identifiants incorrects.'])
            ->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('client')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mobile.client.login');
    }

    public function home(): View
    {
        $clientProfile = auth('client')->user();

        $appointments = $clientProfile->appointments()
            ->where('appointment_date', '>=', now())
            ->with('user')
            ->orderBy('appointment_date')
            ->take(5)
            ->get();

        $invoices = $clientProfile->invoices()
            ->latest()
            ->take(6)
            ->get();

        $messages = Message::where('client_profile_id', $clientProfile->id)
            ->orderByDesc('created_at')
            ->take(4)
            ->get()
            ->reverse()
            ->values();

        $clientFiles = $clientProfile->clientFiles()
            ->latest()
            ->take(6)
            ->get();

        $communityCounts = CommunityMember::query()
            ->where('client_profile_id', $clientProfile->id)
            ->selectRaw("sum(case when status = ? then 1 else 0 end) as pending_count", [CommunityMember::STATUS_INVITED])
            ->selectRaw("sum(case when status = ? then 1 else 0 end) as active_count", [CommunityMember::STATUS_ACTIVE])
            ->first();

        return view('mobile.client.home', [
            'clientProfile' => $clientProfile,
            'appointments' => $appointments,
            'invoices' => $invoices,
            'messages' => $messages,
            'clientFiles' => $clientFiles,
            'pendingInvitesCount' => (int) ($communityCounts->pending_count ?? 0),
            'activeCommunitiesCount' => (int) ($communityCounts->active_count ?? 0),
        ]);
    }

    public function messages(): View
    {
        $client = auth('client')->user();

        $messages = Message::where('client_profile_id', $client->id)
            ->orderBy('created_at')
            ->get();

        return view('mobile.client.messages.index', compact('messages'));
    }

    public function storeMessage(Request $request): RedirectResponse
    {
        $client = auth('client')->user();

        $data = $request->validate([
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $message = Message::create([
            'client_profile_id' => $client->id,
            'user_id' => $client->user_id,
            'sender_type' => 'client',
            'content' => trim($data['content']),
        ]);

        $therapist = $client->user;
        if ($therapist && $therapist->email) {
            Mail::to($therapist->email)->queue(new ClientMessageReceivedTherapistMail($client, $message));
        }

        return redirect()
            ->route('mobile.client.messages.index')
            ->with('success', 'Message envoye.');
    }

    public function storeFile(Request $request): RedirectResponse
    {
        $client = auth('client')->user();

        $data = $request->validate([
            'document' => ['required', 'file', 'max:204800'],
        ]);

        $uploadedFile = $data['document'];
        $path = $uploadedFile->store("client_files/{$client->id}", 'public');

        /** @var ClientFile $clientFile */
        $clientFile = $client->clientFiles()->create([
            'file_path' => $path,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);

        $therapist = $client->user;
        if ($therapist && $therapist->email) {
            Mail::to($therapist->email)->queue(new ClientFileUploadedTherapistMail($client, $clientFile));
        }

        return redirect()
            ->route('mobile.client.home')
            ->with('success', 'Document envoye.');
    }

    public function communities(): View
    {
        $client = auth('client')->user();

        $memberships = CommunityMember::query()
            ->where('client_profile_id', $client->id)
            ->whereIn('status', [CommunityMember::STATUS_INVITED, CommunityMember::STATUS_ACTIVE])
            ->with(['group.user', 'group.channels.pinnedMessage'])
            ->latest()
            ->get();

        $pendingInvites = $memberships->where('status', CommunityMember::STATUS_INVITED)->values();
        $communities = $memberships->where('status', CommunityMember::STATUS_ACTIVE)->values();

        return view('mobile.client.communities.index', compact('pendingInvites', 'communities'));
    }

    public function acceptCommunity(CommunityGroup $community): RedirectResponse
    {
        $client = auth('client')->user();

        $member = CommunityMember::query()
            ->where('community_group_id', $community->id)
            ->where('client_profile_id', $client->id)
            ->firstOrFail();

        abort_unless($member->status === CommunityMember::STATUS_INVITED, 403);

        $member->update([
            'status' => CommunityMember::STATUS_ACTIVE,
            'joined_at' => now(),
            'removed_at' => null,
        ]);

        if ($community->user) {
            $community->user->notify(new CommunityInviteAccepted($community, $member->fresh('clientProfile')));
        }

        return redirect()
            ->route('mobile.client.communities.show', $community)
            ->with('success', 'Vous avez rejoint la communaute.');
    }

    public function showCommunity(Request $request, CommunityGroup $community): View
    {
        $client = auth('client')->user();

        $membership = CommunityMember::query()
            ->where('community_group_id', $community->id)
            ->where('client_profile_id', $client->id)
            ->where('status', CommunityMember::STATUS_ACTIVE)
            ->firstOrFail();

        $community->load([
            'user',
            'channels.pinnedMessage.user',
            'channels.pinnedMessage.clientProfile',
            'channels.pinnedMessage.attachments',
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

        return view('mobile.client.communities.show', [
            'community' => $community,
            'membership' => $membership,
            'selectedChannel' => $selectedChannel,
            'messages' => $messages,
            'attachmentLimitLabel' => UploadLimit::communityAttachmentLimitLabel(),
        ]);
    }

    public function storeCommunityMessage(Request $request, CommunityGroup $community): RedirectResponse
    {
        $client = auth('client')->user();

        CommunityMember::query()
            ->where('community_group_id', $community->id)
            ->where('client_profile_id', $client->id)
            ->where('status', CommunityMember::STATUS_ACTIVE)
            ->firstOrFail();

        if ($community->is_archived) {
            return back()->with('error', 'Cette communaute est archivee.');
        }

        $data = $request->validate([
            'community_channel_id' => ['required', 'integer', 'exists:community_channels,id'],
            'content' => ['required', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => ['file', 'max:' . UploadLimit::communityAttachmentValidationMaxKilobytes(), 'mimes:pdf,jpg,jpeg,png,webp,gif,doc,docx,xls,xlsx,ppt,pptx,txt,csv,mp3,m4a,wav,ogg'],
        ]);

        $channel = CommunityChannel::query()
            ->where('community_group_id', $community->id)
            ->findOrFail((int) $data['community_channel_id']);

        if ($channel->isAnnouncements()) {
            return back()->with('error', 'Seul votre praticien peut publier dans Annonces.');
        }

        $message = $this->messageService->createClientMessage(
            community: $community,
            channel: $channel,
            client: $client,
            content: $data['content'],
            attachments: $request->file('attachments', []),
        );

        if ($community->user) {
            $community->user->notify(new CommunityMessagePosted($message->load(['group', 'channel', 'clientProfile'])));
        }

        return redirect()
            ->route('mobile.client.communities.show', ['community' => $community->id, 'channel' => $channel->id])
            ->with('success', 'Message envoye.');
    }
}
