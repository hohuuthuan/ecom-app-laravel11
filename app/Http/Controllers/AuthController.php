<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Throwable;

class AuthController extends Controller
{
  public function __construct(private readonly AuthService $authService) {}

  public function register(RegisterRequest $request): RedirectResponse
  {
    try {
      if (User::where('email', $request->email)->exists()) {
        return back()
          ->withInput()
          ->withErrors(['email' => 'Email đã được sử dụng']);
      }

      $newAccount = $this->authService->register($request->validated());
      if (!$newAccount) {
        return back()
          ->withErrors('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
      }
    } catch (Throwable $e) {
      return back()
        ->withErrors('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
    }

    return $this->authService->login($request->email, $request->password, false);
  }

  public function login(LoginRequest $request): RedirectResponse
  {
    $remember = $request->boolean('remember');

    return $this->authService->login(
      $request->email,
      $request->password,
      $remember
    );
  }

  public function logout(): RedirectResponse
  {
    $this->authService->logout();
    return redirect()
      ->route('home')
      ->with('success', 'Đăng xuất thành công.');
  }
}
