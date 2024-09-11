<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        // Check if the user is an admin
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            // Redirect non-admins to the homepage with an error message
            return redirect('/')->with('error', 'Unauthorized access');
        }

        // If the user is an admin, show the admin dashboard
        $users = User::all(); // Example: show all registered users
        return view('admin.index', compact('users'));
    }
}
