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
use App\Mail\AdminNewUserNotification;
use Illuminate\Support\Facades\Mail;
use Stripe\Stripe;
use Stripe\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }
    public function createformation(): View
    {
        return view('auth.register-formation');
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
			'g-recaptcha-response' => 'required|captcha',
           
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
    // 1) Normalize email before anything else
    $request->merge([
        'email' => strtolower($request->input('email')),
    ]);

    // 2) Validate the incoming request data
    $request->validate([
        'company_name' => ['nullable', 'string', 'max:255'],
        'services'     => ['nullable', 'string', 'max:255'],
        'about'        => ['nullable', 'string'],
        'name'         => ['required', 'string', 'max:255'],
        // 'lowercase' is now technically redundant since we just forced it,
        // you can keep it or remove it.
        'email'        => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
        'password'     => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    DB::beginTransaction();

    try {
        $user = User::create([
            'company_name'    => $request->company_name,
            // 👉 here, your field "services" in the form is just a text input,
            // so if you don't actually store JSON, you can also just do:
            // 'services' => $request->services,
            'services'        => $request->services ? json_encode($request->services) : null,
            'about'           => $request->about,
            'name'            => $request->name,
            'email'           => $request->email, // already lowercase thanks to merge()
            'password'        => Hash::make($request->password),
            'is_therapist'    => true,
            'license_product' => 'essai',
            'license_status'  => 'actif',
        ]);

        // Generate a unique slug based on the company name and the user id.
        $user->slug = User::createUniqueSlug($request->company_name, $user->id);
        $user->save();

        // (Stripe block is commented out in your code; leaving as-is)

        event(new Registered($user));

        Auth::login($user);

        // Retrieve admin emails
        $adminEmails = User::where('is_admin', true)->pluck('email')->toArray();

        // Send welcome email to the user
        Mail::to($user->email)->send(new WelcomeProMail($user));
        Log::info("Sent WelcomeProMail to user: {$user->email}");

        // Notify admins about the new user
        Mail::to($adminEmails)->send(new AdminNewUserNotification($user));
        Log::info("Queued AdminNewUserNotification for admins.");

        DB::commit();

        return redirect()->route('dashboard-pro');

    } catch (\Exception $e) {
        DB::rollBack();

        Log::error("User Registration Failed: " . $e->getMessage());

        return redirect()->back()
            ->withInput()
            ->withErrors(['registration_error' => 'Registration failed. Please try again.']);
    }
}





    public function storeformation(Request $request): RedirectResponse
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

        return redirect(route('generateTestCertificate', ['name' => $user->name], absolute: false));
    } 


}
