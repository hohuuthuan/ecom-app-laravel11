<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AccountController;

// === PUBLIC ===
Route::view('/', 'user.home')->name('home');

// === GUEST ===
Route::middleware('guest')->group(function () {
  Route::view('/login', 'auth.login')->name('login.form');
  Route::post('/login', [AuthController::class, 'login'])->name('login')->middleware('throttle:10,1');

  Route::view('/register', 'auth.register')->name('register.form');
  Route::post('/register', [AuthController::class, 'register'])->name('register');
});

// === LOGGED IN ===
Route::middleware(['auth'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  Route::prefix('admin')->as('admin.')->middleware('role:Admin')->group(function () {
    Route::get('/dashboard', fn() => view('admin.dashboard'))->name('dashboard');
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts/bulk-update', [AccountController::class, 'bulkUpdate'])->name('accounts.bulk-update');
    Route::put('/accounts/{id}', [AccountController::class, 'updateAccount'])->name('accounts.update');
  });
});

Route::fallback(fn() => response()->view('errors.404', [], 404));
