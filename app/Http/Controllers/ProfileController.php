<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
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
	
	public function editCompanyInfo()
{
    if (!auth()->user()->isTherapist()) {
        abort(403);
    }

    return view('profile.edit-company-info');
}

public function updateCompanyInfo(Request $request)
{
    if (!auth()->user()->isTherapist()) {
        abort(403);
    }

    $validatedData = $request->validate([
        'company_name' => 'nullable|string|max:255',
        'company_address' => 'nullable|string',
        'company_email' => 'nullable|email',
        'company_phone' => 'nullable|string|max:20',
        'legal_mentions' => 'nullable|string',
    ]);

    auth()->user()->update($validatedData);

    return redirect()->route('profile.editCompanyInfo')->with('success', 'Informations mises à jour avec succès.');
}

}
