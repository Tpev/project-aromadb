<?php

namespace App\Http\Controllers;

use App\Models\CommunityMessageAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CommunityAttachmentController extends Controller
{
    public function downloadForPractitioner(CommunityMessageAttachment $attachment): StreamedResponse
    {
        $message = $attachment->message()->with('group')->firstOrFail();

        abort_unless((int) $message->group->user_id === (int) Auth::id(), 403);

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }

    public function downloadForClient(CommunityMessageAttachment $attachment): StreamedResponse
    {
        $client = auth('client')->user();
        $message = $attachment->message()->with('group.members')->firstOrFail();

        $membership = $message->group->members
            ->firstWhere('client_profile_id', $client->id);

        abort_unless($membership && $membership->status === 'active', 403);

        return Storage::disk('public')->download($attachment->file_path, $attachment->original_name);
    }
}
