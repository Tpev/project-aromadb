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

    // Validate the form data
    $validatedData = $request->validate([
        'company_name' => 'nullable|string|max:255',
        'company_address' => 'nullable|string',
        'company_email' => 'nullable|email|max:255',
        'company_phone' => 'nullable|string|max:20',
        'legal_mentions' => 'nullable|string',
        'share_address_publicly' => 'nullable|boolean',
        'share_phone_publicly' => 'nullable|boolean',
        'share_email_publicly' => 'nullable|boolean',
    ]);

    // Get the authenticated user
    $user = auth()->user();

    // Handle checkbox fields, default to false if not present in the request
    $validatedData['share_address_publicly'] = $request->has('share_address_publicly');
    $validatedData['share_phone_publicly'] = $request->has('share_phone_publicly');
    $validatedData['share_email_publicly'] = $request->has('share_email_publicly');

    // Update company information
    $user->update($validatedData);

    // Generate slug if the company name is provided and different from the current one
    if (!empty($validatedData['company_name']) && $user->isDirty('company_name')) {
        $user->slug = User::createUniqueSlug($validatedData['company_name'], $user->id);
        $user->save();
    }

    return redirect()->route('profile.editCompanyInfo')->with('success', 'Informations mises à jour avec succès.');
}
}
