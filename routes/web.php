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



// Route for displaying all blog posts in the index page
Route::get('/article', [BlogPostController::class, 'index'])->name('blog.index');

// Route for displaying individual blog posts using slug
Route::get('/article/{slug}', [BlogPostController::class, 'show'])->name('blog.show');


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

require __DIR__.'/auth.php';
