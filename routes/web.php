<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
  Route::view('/login', 'auth.login')->name('login.form');
  Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:10,1');

  Route::view('/register', 'auth.register')->name('register.form');
  Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::middleware(['web', 'auth'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
  Route::view('/dashboard', 'dashboard')->name('dashboard');
});

Route::prefix('admin')->as('admin.')->middleware(['auth', 'role:admin'])->group(function () {
  Route::view('/', 'admin.dashboard')->name('dashboard');
});

Route::fallback(function () {
  return response()->view('errors.404', [], 404);
});
