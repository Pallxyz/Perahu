<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
| Dashboard USV hanya bisa diakses user yang sudah login (bawaan Breeze).
| Halaman ini SPA di sisi client: 4 tab (Control/Telemetry/Map/System)
| di-switch via JS, bukan reload/route Laravel terpisah.
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

<<<<<<< HEAD
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

=======
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
>>>>>>> 26bce98a8f8cefe8587581fa40afc965acb43053
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

<<<<<<< HEAD
require __DIR__.'/auth.php';
=======
require __DIR__.'/auth.php';
>>>>>>> 26bce98a8f8cefe8587581fa40afc965acb43053
