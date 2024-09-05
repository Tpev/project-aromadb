<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HuileHEController;
use App\Http\Controllers\RecetteController;
use App\Http\Controllers\HuileHVController;

Route::resource('huilehvs', HuileHVController::class);


Route::get('/', [HuileHEController::class, 'index'])->name('huilehes.index');

// Route to display the list of recettes
Route::get('/recettes', [RecetteController::class, 'index'])->name('recettes.index');
// Route to show a single recette
Route::get('/recettes/{recette}', [RecetteController::class, 'show'])->name('recettes.show');


Route::get('/huilehes', [HuileHEController::class, 'index'])->name('huilehes.index');
Route::get('/huilehes/{huileHE}', [HuileHEController::class, 'show'])->name('huilehes.show');


Route::get('huilehvs', [HuileHVController::class, 'index'])->name('huilehvs.index');
Route::get('huilehvs/{huileHV}', [HuileHVController::class, 'show'])->name('huilehvs.show');



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
