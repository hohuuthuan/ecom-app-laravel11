<?php

namespace App\Services\Auth;

use Illuminate\Http\RedirectResponse;

interface AuthServiceInterface
{
  public function register(array $data): bool;
  public function login(string $email, string $password, bool $remember = false): RedirectResponse;
  public function logout(): void;
}
