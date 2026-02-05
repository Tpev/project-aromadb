<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProfileAvatarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user = $request->user();
        $user->fill($validatedData);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null; // Reset email verification if email changes
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Show the form for editing the company info for therapists.
     */
    public function editCompanyInfo()
    {
        if (!auth()->user()->isTherapist()) {
            abort(403);
        }

        $user = auth()->user();
        return view('profile.edit-company-info', compact('user'));
    }

    public function license()
    {
        if (!auth()->user()->isTherapist()) {
            abort(403);
        }

        $user = auth()->user();
        return view('profile.license', compact('user'));
    }

    /**
     * Update the company information for therapists.
     */
    public function updateCompanyInfo(Request $request)
    {
        if (!auth()->user()->isTherapist()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'company_name'  => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:20',
            'legal_mentions' => 'nullable|string',
            'about' => 'nullable|string',
            'minimum_notice_hours' => 'nullable|integer|min:0',
            'services' => 'nullable|string',
            'profile_description' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|mimes:jpeg,png,jpg,gif,svg,heic|max:3048',
            'buffer_time_between_appointments' => 'nullable|integer|min:0',
            'cgv_pdf' => 'nullable|file|mimes:pdf|max:10096',
            'cancellation_notice_hours' => 'nullable|integer|min:0|max:720',

            // ✅ Invoice branding
            'invoice_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:4096',
            'remove_invoice_logo' => 'nullable|boolean',
            'invoice_primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
			'google_event_color_id' => 'nullable|in:1,2,3,4,5,6,7,8,9,10,11',

        ]);

        $user = auth()->user();

        // Checkbox fields
        $user->share_address_publicly = $request->has('share_address_publicly');
        $user->share_email_publicly   = $request->has('share_email_publicly');
        $user->share_phone_publicly   = $request->has('share_phone_publicly');
        $user->accept_online_appointments = $request->has('accept_online_appointments');

        // Update company information except 'services'
        $user->fill([
            'company_name' => $validatedData['company_name'] ?? null,
            'company_address' => $validatedData['company_address'] ?? null,
            'company_email' => $validatedData['company_email'] ?? null,
            'company_phone' => $validatedData['company_phone'] ?? null,
            'legal_mentions' => $validatedData['legal_mentions'] ?? null,
            'about' => $validatedData['about'] ?? null,
            'minimum_notice_hours' => $validatedData['minimum_notice_hours'] ?? null,
            'profile_description' => $validatedData['profile_description'] ?? null,
            'buffer_time_between_appointments' => $validatedData['buffer_time_between_appointments'] ?? null,
        ]);
		// Google Calendar event color (store only palette ID)
		if ($request->filled('google_event_color_id')) {
			$user->google_event_color_id = (string) $request->input('google_event_color_id');
		} else {
			// If not provided, keep existing value (your sync code already defaults to 9)
			// $user->google_event_color_id = $user->google_event_color_id;
		}

        // Process services
        $user->services = json_decode($validatedData['services'] ?? '[]', true) ?? [];

        /* ──────────── PROFILE PICTURE HANDLING ──────────── */
        if ($request->hasFile('profile_picture')) {
            $path320 = ProfileAvatarService::store(
                $request->file('profile_picture'),
                $user->id
            );
            $user->profile_picture = $path320;
        }

        /* ──────────── CGV PDF HANDLING ──────────── */
        if ($request->hasFile('cgv_pdf')) {
            if ($user->cgv_pdf_path && Storage::disk('public')->exists($user->cgv_pdf_path)) {
                Storage::disk('public')->delete($user->cgv_pdf_path);
            }

            $cgvPath = $request->file('cgv_pdf')->store('cgv', 'public');
            $user->cgv_pdf_path = $cgvPath;
        }

        /* ──────────── INVOICE LOGO HANDLING ──────────── */
        if ($request->boolean('remove_invoice_logo')) {
            if ($user->invoice_logo_path && Storage::disk('public')->exists($user->invoice_logo_path)) {
                Storage::disk('public')->delete($user->invoice_logo_path);
            }
            $user->invoice_logo_path = null;
        }

        if ($request->hasFile('invoice_logo')) {
            if ($user->invoice_logo_path && Storage::disk('public')->exists($user->invoice_logo_path)) {
                Storage::disk('public')->delete($user->invoice_logo_path);
            }

            $ext = $request->file('invoice_logo')->getClientOriginalExtension();
            $fileName = 'logo_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $ext;
            $path = $request->file('invoice_logo')->storeAs('invoice_logos/' . $user->id, $fileName, 'public');

            $user->invoice_logo_path = $path;
        }

        // Invoice primary color
        if (array_key_exists('invoice_primary_color', $validatedData)) {
            $user->invoice_primary_color = $validatedData['invoice_primary_color'] ?: null;
        }

        /* ────────────── SLUG (if company name changed) ───────────── */
        if (!empty($validatedData['company_name']) && $user->isDirty('company_name')) {
            $user->slug = User::createUniqueSlug($validatedData['company_name'], $user->id);
        }

        $user->cancellation_notice_hours = (int) ($request->input('cancellation_notice_hours', 0));

        $user->save();

        return redirect()->route('profile.editCompanyInfo')->with('success', 'Informations mises à jour avec succès.');
    }

    public function submitOnboarding(Request $request)
    {
        if (!auth()->user()->isTherapist()) {
            abort(403);
        }

        $validatedData = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'required|string',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:20',
            'legal_mentions' => 'nullable|string',
            'about' => 'nullable|string',
            'minimum_notice_hours' => 'nullable|integer|min:0',
            'services' => 'nullable|string',
            'profile_description' => 'nullable|string|max:1000',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,heic|max:3048',
            'buffer_time_between_appointments' => 'nullable|integer|min:0',
            'cgv_pdf' => 'nullable|file|mimes:pdf|max:10096',

            // ✅ Invoice branding (optional on onboarding)
            'invoice_logo' => 'nullable|image|mimes:png,jpg,jpeg,webp,svg|max:4096',
            'invoice_primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        $user = auth()->user();

        $user->share_address_publicly = $request->has('share_address_publicly');
        $user->share_email_publicly   = $request->has('share_email_publicly');
        $user->share_phone_publicly   = $request->has('share_phone_publicly');
        $user->accept_online_appointments = $request->has('accept_online_appointments');

        $user->fill([
            'company_name' => $validatedData['company_name'],
            'company_address' => $validatedData['company_address'],
            'company_email' => $validatedData['company_email'],
            'company_phone' => $validatedData['company_phone'],
            'legal_mentions' => $validatedData['legal_mentions'] ?? null,
            'about' => $validatedData['about'] ?? null,
            'minimum_notice_hours' => $validatedData['minimum_notice_hours'] ?? null,
            'profile_description' => $validatedData['profile_description'] ?? null,
            'buffer_time_between_appointments' => $validatedData['buffer_time_between_appointments'] ?? null,
        ]);

        $user->services = json_decode($validatedData['services'] ?? '[]', true) ?? [];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        /* ──────────── CGV PDF HANDLING ──────────── */
        if ($request->hasFile('cgv_pdf')) {
            if ($user->cgv_pdf_path && Storage::disk('public')->exists($user->cgv_pdf_path)) {
                Storage::disk('public')->delete($user->cgv_pdf_path);
            }

            $cgvPath = $request->file('cgv_pdf')->store('cgv', 'public');
            $user->cgv_pdf_path = $cgvPath;
        }

        /* ──────────── INVOICE LOGO HANDLING ──────────── */
        if ($request->hasFile('invoice_logo')) {
            if ($user->invoice_logo_path && Storage::disk('public')->exists($user->invoice_logo_path)) {
                Storage::disk('public')->delete($user->invoice_logo_path);
            }

            $ext = $request->file('invoice_logo')->getClientOriginalExtension();
            $fileName = 'logo_' . now()->format('Ymd_His') . '_' . uniqid() . '.' . $ext;
            $path = $request->file('invoice_logo')->storeAs('invoice_logos/' . $user->id, $fileName, 'public');

            $user->invoice_logo_path = $path;
        }

        if (array_key_exists('invoice_primary_color', $validatedData)) {
            $user->invoice_primary_color = $validatedData['invoice_primary_color'] ?: null;
        }

        if (!empty($validatedData['company_name']) && $user->isDirty('company_name')) {
            $user->slug = User::createUniqueSlug($validatedData['company_name'], $user->id);
        }

        $user->save();

        return redirect()->route('dashboard-pro')->with('success', 'Bienvenue ! Votre profil a été créé avec succès.');
    }

    public function showOnboardingForm()
    {
        if (!auth()->user()->isTherapist()) {
            abort(403);
        }

        if (auth()->user()->is_onboarded) {
            return redirect()->route('dashboard')->with('info', 'Vous avez déjà complété votre profil.');
        }

        return view('onboarding');
    }
}
