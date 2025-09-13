<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\CatalogPageController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\CategoryController;

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

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts/bulk-update', [AccountController::class, 'bulkUpdate'])->name('accounts.bulk-update');
    Route::put('/accounts/{id}', [AccountController::class, 'updateAccount'])->name('accounts.update');

    // Catalog
    Route::get('/catalog', [CatalogPageController::class,'index'])->name('catalog.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{id}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::post('/brands/bulk-delete', [BrandController::class, 'bulkDelete'])->name('brands.bulk-delete');

    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('categories.bulk-delete');
  });
});

Route::fallback(fn() => response()->view('errors.404', [], 404));
