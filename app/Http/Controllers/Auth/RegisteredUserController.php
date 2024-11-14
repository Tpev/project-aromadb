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
        // Validate the incoming request data
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Use a database transaction to ensure atomicity
        DB::beginTransaction();

        try {
            // Create the user without the stripe_customer_id initially
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_therapist' => true,  // Set to true for therapist registration
				'license_product' => 'essai',
				'license_status' => 'actif',
            ]);

            // Initialize Stripe with the secret key from config
            Stripe::setApiKey(config('services.stripe.secret'));

            // Create a Stripe customer for the user
            $customer = Customer::create([
                'email' => $user->email,
                'name' => $user->name,
                'metadata' => [
                    'user_id' => $user->id,
                ],
            ]);

            // Associate the Stripe customer ID with the user
            $user->stripe_customer_id = $customer->id;
            $user->save();

            Log::info("Created Stripe customer for user: {$user->email} (Customer ID: {$customer->id})");


            // Fire the Registered event
            event(new \Illuminate\Auth\Events\Registered($user));

            // Log the user in
            Auth::login($user);

            // Retrieve admin emails
            $adminEmails = User::where('is_admin', true)->pluck('email')->toArray();

            // Send welcome email to the user
            Mail::to($user->email)->send(new WelcomeProMail($user));
            Log::info("Sent WelcomeProMail to user: {$user->email}");

            // Notify admins about the new user
            Mail::to($adminEmails)->queue(new AdminNewUserNotification($user));
            Log::info("Queued AdminNewUserNotification for admins.");

            // Commit the transaction
            DB::commit();

            // Redirect to the therapist dashboard
            return redirect()->route('dashboard-pro');

        } catch (\Exception $e) {
            // Rollback the transaction on error
            DB::rollBack();

            // Log the error for debugging
            Log::error("User Registration Failed: " . $e->getMessage());

            // Optionally, you can redirect back with an error message
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
