<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Mail\TherapistInviteMail;
use App\Models\ReferralInvite;
use App\Models\User;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class MobileReferralController extends Controller
{
    public function __construct(private ReferralService $referrals)
    {
    }

    public function index()
    {
        $this->requireTherapist();

        $user = Auth::user();
        $code = $this->referrals->getOrCreateCodeFor($user);
        $shareUrl = url('/register-pro?ref=' . urlencode($code->code));

        $invites = ReferralInvite::query()
            ->where('referrer_user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $referredUsersCount = User::query()
            ->where('referred_by_user_id', $user->id)
            ->count();

        $referredUsersPaidCount = User::query()
            ->where('referred_by_user_id', $user->id)
            ->whereNotNull('referral_converted_at')
            ->count();

        return view('mobile.referrals.index', [
            'code' => $code,
            'shareUrl' => $shareUrl,
            'invites' => $invites,
            'referredUsersCount' => $referredUsersCount,
            'referredUsersPaidCount' => $referredUsersPaidCount,
        ]);
    }

    public function invite(Request $request)
    {
        $this->requireTherapist();

        $user = Auth::user();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $todayCount = ReferralInvite::query()
            ->where('referrer_user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($todayCount >= 20) {
            return back()->withErrors([
                'email' => 'Limite atteinte : 20 invitations par jour.',
            ])->withInput();
        }

        $normalizedEmail = mb_strtolower(trim($validated['email']));

        $existing = ReferralInvite::query()
            ->where('referrer_user_id', $user->id)
            ->where('email', $normalizedEmail)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        if ($existing) {
            return redirect()
                ->route('mobile.referrals.index')
                ->with('success', 'Invitation deja envoyee recemment a ' . $existing->email . '.');
        }

        $invite = $this->referrals->createInvite($user, $normalizedEmail, $validated['message'] ?? null);
        $code = $this->referrals->getOrCreateCodeFor($user);

        $signupUrl = route('pro.referrals.accept', [
            'token' => $invite->token,
            'ref' => $code->code,
        ]);

        Mail::to($invite->email)->send(new TherapistInviteMail($invite, $user, $signupUrl));

        return redirect()
            ->route('mobile.referrals.index')
            ->with('success', 'Invitation envoyee a ' . $invite->email . '.');
    }

    public function resend(ReferralInvite $invite)
    {
        $this->requireTherapist();

        $user = Auth::user();

        if ((int) $invite->referrer_user_id !== (int) $user->id) {
            abort(403, 'Unauthorized action.');
        }

        if ($invite->isExpired()) {
            return redirect()
                ->route('mobile.referrals.index')
                ->withErrors(['email' => 'Cette invitation est expiree. Creez-en une nouvelle.']);
        }

        $code = $this->referrals->getOrCreateCodeFor($user);
        $signupUrl = route('pro.referrals.accept', [
            'token' => $invite->token,
            'ref' => $code->code,
        ]);

        Mail::to($invite->email)->send(new TherapistInviteMail($invite, $user, $signupUrl));

        return redirect()
            ->route('mobile.referrals.index')
            ->with('success', 'Invitation renvoyee a ' . $invite->email . '.');
    }

    private function requireTherapist(): void
    {
        $user = Auth::user();

        if (!$user || !$user->is_therapist) {
            abort(403, 'Unauthorized action.');
        }
    }
}
