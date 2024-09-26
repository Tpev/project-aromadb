<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HuileHEController;
use App\Http\Controllers\RecetteController;
use App\Http\Controllers\HuileHVController;
use App\Http\Controllers\FavoriteController;
use App\Models\Favorite;
use App\Http\Controllers\TisaneController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SessionNoteController;


// Session Notes Routes

Route::middleware(['auth', 'can:viewAny,App\Models\ClientProfile'])->group(function () {
    Route::get('/client_profiles/{clientProfile}/session_notes', [SessionNoteController::class, 'index'])->name('session_notes.index');
    Route::get('/client_profiles/{clientProfile}/session_notes/create', [SessionNoteController::class, 'create'])->name('session_notes.create');
    Route::post('/client_profiles/{clientProfile}/session_notes', [SessionNoteController::class, 'store'])->name('session_notes.store');
    Route::get('/session_notes/{sessionNote}', [SessionNoteController::class, 'show'])->name('session_notes.show');
    Route::get('/session_notes/{sessionNote}/edit', [SessionNoteController::class, 'edit'])->name('session_notes.edit');
    Route::put('/session_notes/{sessionNote}', [SessionNoteController::class, 'update'])->name('session_notes.update');
    Route::delete('/session_notes/{sessionNote}', [SessionNoteController::class, 'destroy'])->name('session_notes.destroy');
});





// Client Profiles Routes
Route::middleware(['auth','can:viewAny,App\Models\ClientProfile'])->group(function () {
    Route::get('/client_profiles', [ClientProfileController::class, 'index'])->name('client_profiles.index'); // Show all client profiles
    Route::get('/client_profiles/create', [ClientProfileController::class, 'create'])->name('client_profiles.create'); // Show form to create a client profile
    Route::post('/client_profiles', [ClientProfileController::class, 'store'])->name('client_profiles.store'); // Handle form submission for creating a client profile
    Route::get('/client_profiles/{clientProfile}', [ClientProfileController::class, 'show'])->name('client_profiles.show');
    Route::get('/client_profiles/{clientProfile}/edit', [ClientProfileController::class, 'edit'])->name('client_profiles.edit'); // Show form to edit a client profile
    Route::put('/client_profiles/{clientProfile}', [ClientProfileController::class, 'update'])->name('client_profiles.update'); // Handle form submission for updating a client profile
    Route::delete('/client_profiles/{clientProfile}', [ClientProfileController::class, 'destroy'])->name('client_profiles.destroy'); // Handle the deletion of a client profile
});



Route::middleware(['auth','can:viewAny,App\Models\ClientProfile'])->group(function () {
    Route::get('/appointments', [App\Http\Controllers\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [App\Http\Controllers\AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointments.destroy');
});






Route::get('/sitemap', [SitemapController::class, 'index']);


Route::middleware([\App\Http\Middleware\TrackPageViews::class])->group(function () {

    // Route to the welcome page directly returning the welcome view
    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');

    // Other routes
    Route::get('tisanes', [TisaneController::class, 'index'])->name('tisanes.index');
    Route::get('/recettes', [RecetteController::class, 'index'])->name('recettes.index');
    Route::get('/huilehes', [HuileHEController::class, 'index'])->name('huilehes.index');
    Route::get('huilehvs', [HuileHVController::class, 'index'])->name('huilehvs.index');
	
    Route::get('/huilehes/{slug}', [HuileHEController::class, 'show'])->name('huilehes.show');
    Route::get('/huilehvs/{slug}', [HuileHVController::class, 'show'])->name('huilehvs.show');
    Route::get('/recettes/{slug}', [RecetteController::class, 'show'])->name('recettes.show');
    Route::get('/tisanes/{slug}', [TisaneController::class, 'show'])->name('tisanes.show');
	
	    Route::get('/IntroductionAromatherapie', function () {
        return view('formation1');
    })->name('formation1');
	
	 Route::get('/huilehe/proprietes', [HuileHEController::class, 'showhuilehepropriete'])->name('huilehes.showhuilehepropriete');
	// Route for displaying all blog posts in the index page
Route::get('/article', [BlogPostController::class, 'index'])->name('blog.index');

// Route for displaying individual blog posts using slug
Route::get('/article/{slug}', [BlogPostController::class, 'show'])->name('blog.show');
});




Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.index');




Route::post('/favorites/toggle/{type}/{id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');




//test

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




    // Route to the privacy policy page 
    Route::get('/privacy-policy', function () {
        return view('privacypolicy');
    })->name('privacypolicy');






require __DIR__.'/auth.php';
