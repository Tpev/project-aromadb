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

    // Get the page view stats: group by session_id and url, count the views, and get the last viewed timestamp for each page
    $pageViews = PageViewLog::select('url', 'session_id', \DB::raw('COUNT(*) as view_count'), \DB::raw('MAX(viewed_at) as last_viewed_at'))
        ->groupBy('url', 'session_id')
        ->orderByDesc('last_viewed_at')
        ->get();

    return view('admin.index', compact('users', 'pageViews'));
}

}
