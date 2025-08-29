<?php

namespace App\Providers;

use App\Services\Auth\AuthServiceInterface;
use App\Services\Auth\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\SessionGuard;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    $this->app->bind(AuthServiceInterface::class, AuthService::class);
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void {}
}
