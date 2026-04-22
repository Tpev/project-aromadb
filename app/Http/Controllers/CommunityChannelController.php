<?php

namespace App\Http\Controllers;

use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityChannelController extends Controller
{
    public function store(Request $request, CommunityGroup $community): RedirectResponse
    {
        abort_unless((int) $community->user_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'channel_type' => 'required|in:' . implode(',', [
                CommunityChannel::TYPE_DISCUSSION,
                CommunityChannel::TYPE_ANNOUNCEMENTS,
            ]),
        ]);

        $community->channels()->create([
            'name' => trim($data['name']),
            'description' => trim((string) ($data['description'] ?? '')) ?: null,
            'channel_type' => $data['channel_type'],
            'position' => ((int) $community->channels()->max('position')) + 1,
        ]);

        return redirect()
            ->route('communities.show', $community)
            ->with('success', 'Salon ajoute.');
    }
}
