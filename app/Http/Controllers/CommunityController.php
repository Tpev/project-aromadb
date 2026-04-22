<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Support\UploadLimit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CommunityController extends Controller
{
    public function index(): View
    {
        $communities = CommunityGroup::query()
            ->where('user_id', Auth::id())
            ->withCount([
                'activeMembers as active_members_count',
                'invitedMembers as invited_members_count',
                'channels',
                'messages',
            ])
            ->with(['channels.pinnedMessage'])
            ->latest()
            ->get();

        return view('communities.index', compact('communities'));
    }

    public function create(): View
    {
        return view('communities.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $community = CommunityGroup::create([
            'user_id' => Auth::id(),
            'name' => trim($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
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
            ->route('communities.show', $community)
            ->with('success', 'Communauté créée avec succès.');
    }

    public function show(Request $request, CommunityGroup $community): View
    {
        $this->authorizeCommunity($community);

        $community->load([
            'user',
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
                ->take(100)
                ->get()
                ->reverse()
                ->values();
        }

        return view('communities.show', [
            'community' => $community,
            'selectedChannel' => $selectedChannel,
            'messages' => $messages,
            'attachmentLimitLabel' => UploadLimit::communityAttachmentLimitLabel(),
        ]);
    }

    public function manage(CommunityGroup $community): View
    {
        $this->authorizeCommunity($community);

        $community->load([
            'user',
            'channels.pinnedMessage',
            'members.clientProfile',
        ]);

        $availableClients = ClientProfile::query()
            ->where('user_id', Auth::id())
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('communities.manage', [
            'community' => $community,
            'availableClients' => $availableClients,
        ]);
    }

    public function edit(CommunityGroup $community): View
    {
        $this->authorizeCommunity($community);

        return view('communities.edit', compact('community'));
    }

    public function update(Request $request, CommunityGroup $community): RedirectResponse
    {
        $this->authorizeCommunity($community);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'is_archived' => 'nullable|boolean',
        ]);

        $community->update([
            'name' => trim($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'is_archived' => $request->boolean('is_archived'),
        ]);

        return redirect()
            ->route('communities.show', $community)
            ->with('success', 'Communauté mise à jour.');
    }

    protected function authorizeCommunity(CommunityGroup $community): void
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);
    }
}
