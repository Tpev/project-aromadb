<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PageViewLog;

class AdminController extends Controller
{
public function index()
{
    // Check if the user is an admin
    if (!auth()->user() || !auth()->user()->isAdmin()) {
        return redirect('/')->with('error', 'Unauthorized access');
    }

    // Get the list of users
    $users = User::all();

    // Get the page view stats: group by URL, count the views, and get the last viewed timestamp
    $pageViews = PageViewLog::select('url', \DB::raw('COUNT(*) as view_count'), \DB::raw('MAX(created_at) as viewed_at'))
        ->groupBy('url')
        ->orderByDesc('view_count')
        ->get();

    return view('admin.index', compact('users', 'pageViews'));
}
}
