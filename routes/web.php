<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// === PUBLIC ===
Route::view('/', 'user.home')->name('home');

// === GUEST ===
Route::middleware('guest')->group(function () {
  Route::view('/login', 'auth.login')->name('login.form');
  Route::post('/login', [AuthController::class, 'login'])
    ->name('login')
    ->middleware('throttle:10,1');

  Route::view('/register', 'auth.register')->name('register.form');
  Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// === LOGGED IN ===
Route::middleware(['auth'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  Route::prefix('admin')->as('admin.')->middleware('role:admin')->group(function () {
    Route::view('/dashboard', 'admin.dashboard')->name('dashboard');
  });
});

Route::fallback(fn () => response()->view('errors.404', [], 404));
