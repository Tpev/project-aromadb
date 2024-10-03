<?php

namespace App\Http\Controllers;

use App\Models\LicenseHistory;
use Illuminate\Http\Request;

class LicenseHistoryController extends Controller
{
    /**
     * Display the history of licenses for all users.
     */
    public function index()
    {
        $histories = LicenseHistory::with('user', 'licenseTier')->get();
        return view('license-history.index', compact('histories'));
    }

    /**
     * Show a user's specific license history.
     */
    public function show($userId)
    {
        $histories = LicenseHistory::where('user_id', $userId)->with('licenseTier')->get();
        return view('license-history.show', compact('histories'));
    }
}
