<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LicenseTier;
use App\Models\UserLicense;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Carbon\Carbon;
use App\Mail\WelcomeProMail;
use Illuminate\Support\Facades\Mail;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }   
	public function createpro(): View
    {
        return view('auth.register-pro');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
			'is_therapist' => ['boolean'],
           
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
			'is_therapist' => $data['is_therapist'] ?? false,  // Default to false if not provided
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }    
    /**
     * Handle an incoming registration request for pro users (therapists).
     *
     * @throws \Illuminate\Validation\ValidationException
     */
   public function storepro(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'is_therapist' => true,  // Set to true for therapist registration
    ]);

    // Fetch the trial license tier
    $trialLicenseTier = LicenseTier::where('is_trial', true)->first();

    if ($trialLicenseTier) {
        // Assign a trial license to the user
        UserLicense::create([
            'user_id' => $user->id,
            'license_tier_id' => $trialLicenseTier->id,
            'start_date' => Carbon::now(),
            'expiration_date' => Carbon::now()->addDays($trialLicenseTier->trial_duration_days), // Set expiration based on trial duration
        ]);
    }

    event(new Registered($user));
    Auth::login($user);

    // Envoyer l'e-mail de bienvenue
    Mail::to($user->email)->send(new WelcomeProMail($user));

    return redirect()->route('onboarding');
}

}
