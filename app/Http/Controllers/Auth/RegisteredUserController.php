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
    // Validate the incoming request data, with the new fields set as optional.
    $request->validate([
        'company_name' => ['nullable', 'string', 'max:255'],
        'services'     => ['nullable', 'string', 'max:255'],
        'about'        => ['nullable', 'string'],
        'name'         => ['required', 'string', 'max:255'],
        'email'        => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
        'password'     => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    // Use a database transaction to ensure atomicity
    DB::beginTransaction();

    try {
        // Create the user with the new fields along with the registration data.
        $user = User::create([
            'company_name'   => $request->company_name,
            // If services is provided, cast it to valid JSON, otherwise store null.
            'services'       => $request->services ? json_encode($request->services) : null,
            'about'          => $request->about,
            'name'           => $request->name,
            'email'          => $request->email,
            'password'       => Hash::make($request->password),
            'is_therapist'   => true,  // Set to true for therapist registration
            'license_product'=> 'essai',
            'license_status' => 'actif',
        ]);

        // Generate a unique slug based on the company name and the user id.
        $user->slug = User::createUniqueSlug($request->company_name, $user->id);
        $user->save();

/*        // Stripe integration can be added here if needed.
        Stripe::setApiKey(config('services.stripe.secret'));
        $customer = Customer::create([
            'email' => $user->email,
            'name'  => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);
        $user->stripe_customer_id = $customer->id; */
       

        // Fire the Registered event
        event(new Registered($user));

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

        // Redirect back with an error message
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
