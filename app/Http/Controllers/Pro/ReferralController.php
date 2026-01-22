<?php

namespace App\Http\Controllers\Pro;

use App\Http\Controllers\Controller;
use App\Mail\TherapistInviteMail;
use App\Models\ReferralInvite;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
class ReferralController extends Controller
{
    public function __construct(private ReferralService $referrals)
    {
        // no middleware here (you said you don't want / don't use it)
    }

    private function requireAuth(): void
    {
        if (!Auth::id()) {
            abort(403);
        }
    }

    private function requireTherapist(): void
    {
        $this->requireAuth();

        $user = Auth::user();
        if (!$user || !$user->is_therapist) {
            abort(403);
        }
    }

public function index()
{
    $this->requireTherapist();

    $user = Auth::user();

    $code = $this->referrals->getOrCreateCodeFor($user);

    $invites = ReferralInvite::where('referrer_user_id', $user->id)
        ->orderByDesc('created_at')
        ->get();

    // ✅ Stats basées sur les inscriptions réelles (par code ou invite)
    $referredUsersCount = User::where('referred_by_user_id', $user->id)->count();

    // ✅ "Payants" via conversion utilisateur (quand tu appelles markConverted)
    $referredUsersPaidCount = User::where('referred_by_user_id', $user->id)
        ->whereNotNull('referral_converted_at')
        ->count();

    // Si ton register pro est /register-pro :
    $shareUrl = url('/register-pro?ref=' . urlencode($code->code));

    return view('pro.referrals.index', compact(
        'code',
        'invites',
        'shareUrl',
        'referredUsersCount',
        'referredUsersPaidCount'
    ));
}


    public function invite(Request $request)
    {
        $this->requireTherapist();

        $user = Auth::user();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        // simple anti-spam
        $todayCount = ReferralInvite::where('referrer_user_id', $user->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($todayCount >= 20) {
            return back()->withErrors([
                'email' => "Limite atteinte : 20 invitations par jour.",
            ])->withInput();
        }

        // Avoid re-inviting same email too often
        $normalizedEmail = mb_strtolower(trim($validated['email']));

        $existing = ReferralInvite::where('referrer_user_id', $user->id)
            ->where('email', $normalizedEmail)
            ->where('created_at', '>=', now()->subDays(7))
            ->first();

        if ($existing) {
            return back()->with('success', "Invitation déjà envoyée récemment à {$existing->email}.");
        }

        $invite = $this->referrals->createInvite($user, $normalizedEmail, $validated['message'] ?? null);
        $code = $this->referrals->getOrCreateCodeFor($user);

        // Public accept endpoint that tracks open and redirects to registration
        $signupUrl = route('pro.referrals.accept', [
            'token' => $invite->token,
            'ref' => $code->code,
        ]);

        Mail::to($invite->email)->send(new TherapistInviteMail($invite, $user, $signupUrl));

        return back()->with('success', "Invitation envoyée à {$invite->email} ✅");
    }

    public function resend(ReferralInvite $invite)
    {
        $this->requireTherapist();

        $user = Auth::user();

        if ($invite->referrer_user_id !== $user->id) {
            abort(403);
        }

        if ($invite->isExpired()) {
            return back()->withErrors(['email' => "Cette invitation est expirée. Créez-en une nouvelle."]);
        }

        $code = $this->referrals->getOrCreateCodeFor($user);

        $signupUrl = route('pro.referrals.accept', [
            'token' => $invite->token,
            'ref' => $code->code,
        ]);

        Mail::to($invite->email)->send(new TherapistInviteMail($invite, $user, $signupUrl));

        return back()->with('success', "Invitation renvoyée à {$invite->email} ✅");
    }

    /**
     * Public endpoint:
     * - marks opened
     * - redirects to register-pro with invite+ref
     */
    public function accept(Request $request, string $token)
    {
        $invite = ReferralInvite::where('token', $token)->firstOrFail();

        if ($invite->isExpired()) {
            $invite->update(['status' => 'expired']);
            return redirect('/register-pro')->with('error', "Invitation expirée.");
        }

        if ($invite->status === 'sent') {
            $invite->update([
                'status' => 'opened',
                'opened_at' => now(),
            ]);
        }

        $ref = $request->query('ref');

        return redirect('/register-pro?invite=' . urlencode($token) . ($ref ? '&ref=' . urlencode($ref) : ''));
    }
}
