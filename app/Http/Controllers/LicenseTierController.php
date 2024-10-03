<?php

namespace App\Http\Controllers;

use App\Models\LicenseTier;
use Illuminate\Http\Request;

class LicenseTierController extends Controller
{
    /**
     * Display a listing of the license tiers.
     */
    public function index()
    {
        $tiers = LicenseTier::all();
        return view('license-tiers.index', compact('tiers'));
    }

    /**
     * Show the form for creating a new license tier.
     */
    public function create()
    {
        return view('license-tiers.create');
    }

    /**
     * Store a newly created license tier in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'is_trial' => 'boolean',
            'trial_duration_days' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'features' => 'required|array',
        ]);

        LicenseTier::create($request->all());
        return redirect()->route('license-tiers.index')->with('success', 'License tier created successfully.');
    }

    /**
     * Show the form for editing the specified license tier.
     */
    public function edit(LicenseTier $licenseTier)
    {
        return view('license-tiers.edit', compact('licenseTier'));
    }

    /**
     * Update the specified license tier in storage.
     */
    public function update(Request $request, LicenseTier $licenseTier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'is_trial' => 'boolean',
            'trial_duration_days' => 'nullable|integer|min:1',
            'price' => 'required|numeric|min:0',
            'features' => 'required|array',
        ]);

        $licenseTier->update($request->all());
        return redirect()->route('license-tiers.index')->with('success', 'License tier updated successfully.');
    }

    /**
     * Remove the specified license tier from storage.
     */
    public function destroy(LicenseTier $licenseTier)
    {
        $licenseTier->delete();
        return redirect()->route('license-tiers.index')->with('success', 'License tier deleted successfully.');
    }
}
