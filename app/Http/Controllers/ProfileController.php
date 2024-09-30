<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;

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
            'name' => 'required|string|max:255',
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

    /**
     * Update the company information for therapists.
     */
public function updateCompanyInfo(Request $request)
{
    if (!auth()->user()->isTherapist()) {
        abort(403);
    }

    // Validate the form data, including the new fields
    $validatedData = $request->validate([
        'company_name' => 'nullable|string|max:255',
        'company_address' => 'nullable|string',
        'company_email' => 'nullable|email|max:255',
        'company_phone' => 'nullable|string|max:20',
        'legal_mentions' => 'nullable|string',
        'about' => 'nullable|string',
        'minimum_notice_hours' => 'nullable|integer|min:0', // Updated validation rule
        'services' => 'nullable|string', // Expect services as a JSON string
        'profile_description' => 'nullable|string|max:1000',
        'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Max 2MB
        //'accept_online_appointments' => 'sometimes|boolean', // Validation rule for boolean
    ]);

    // Get the authenticated user
    $user = auth()->user();

    // Handle checkbox fields. If not present in the request, set them to false.
    $user->share_address_publicly = $request->has('share_address_publicly');
    $user->share_email_publicly = $request->has('share_email_publicly'); // Corrected line
    $user->share_phone_publicly = $request->has('share_phone_publicly');
    $user->accept_online_appointments = $request->has('accept_online_appointments'); // Handle boolean

    // Update company information except 'services'
    $user->fill([
        'company_name' => $validatedData['company_name'],
        'company_address' => $validatedData['company_address'],
        'company_email' => $validatedData['company_email'],
        'company_phone' => $validatedData['company_phone'],
        'legal_mentions' => $validatedData['legal_mentions'],
        'about' => $validatedData['about'],
        'minimum_notice_hours' => $validatedData['minimum_notice_hours'],
        'profile_description' => $validatedData['profile_description'], // Handle profile description
    ]);

    // Process services (stored as JSON string in the input field)
    $user->services = json_decode($validatedData['services'], true) ?? [];

    // Handle profile picture upload
    if ($request->hasFile('profile_picture')) {
        // Delete the old profile picture if it exists
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        // Store the new profile picture in 'profile_pictures' directory within 'public' disk
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Update the user's profile picture path
        $user->profile_picture = $path;
    }

    // Generate slug if the company name is provided and different from the current one
    if (!empty($validatedData['company_name']) && $user->isDirty('company_name')) {
        $user->slug = User::createUniqueSlug($validatedData['company_name'], $user->id);
    }

    // Save user
    $user->save();

    return redirect()->route('profile.editCompanyInfo')->with('success', 'Informations mises à jour avec succès.');
}




}
