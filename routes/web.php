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

Route::get('/sitemap', [SitemapController::class, 'index']);




Route::resource('huilehvs', HuileHVController::class);


Route::get('tisanes', [TisaneController::class, 'index'])->name('tisanes.index');
Route::get('tisanes/{slug}', [TisaneController::class, 'show'])->name('tisanes.show');

Route::get('/', [HuileHEController::class, 'index'])->name('huilehes.index');

// Route to display the list of recettes
Route::get('/recettes', [RecetteController::class, 'index'])->name('recettes.index');
// Route to show a single recette
Route::get('/recettes/{slug}', [RecetteController::class, 'show'])->name('recettes.show');

Route::get('/huilehe/{slug}', [HuileHEController::class, 'show'])->name('huilehes.show');

Route::get('/huilehes', [HuileHEController::class, 'index'])->name('huilehes.index');
Route::get('/huilehes/{huileHE}', [HuileHEController::class, 'show'])->name('huilehes.show');

Route::get('/huilehvs/{slug}', [HuileHVController::class, 'show'])->name('huilehvs.show');
Route::get('huilehvs', [HuileHVController::class, 'index'])->name('huilehvs.index');
Route::get('huilehvs/{huileHV}', [HuileHVController::class, 'show'])->name('huilehvs.show');


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
