<?php

namespace App\Services\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Role;

class AuthService
{
  public function register(array $data): bool
  {
    try {
      DB::transaction(function () use ($data) {
        $user = User::create([
          'id'        => Str::uuid()->toString(),
          'email'     => $data['email'],
          'password'  => Hash::make($data['password']),
          'full_name' => $data['full_name'],
          'phone'     => $data['phone'],
          'status'    => 'ACTIVE',
        ]);

        $customerId = Role::where('name', 'Customer')->value('id');
        $user->roles()->attach($customerId);
      });

      return true;
    } catch (\Throwable $e) {
      Log::error('Register failed', ['msg' => $e->getMessage()]);
      return false;
    }
  }

  public function login(string $email, string $password, bool $remember = false): RedirectResponse
  {
    $sessionId    = session()->getId();
    $key          = 'login:' . sha1($sessionId . '|' . request()->ip());
    $maxAttempts  = 6;
    $decaySeconds = 15 * 60;
    if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
      $seconds = RateLimiter::availableIn($key);
      session()->flash('toast_error', "Bạn thử quá nhiều lần. Vui lòng thử lại sau {$seconds}s.");
      return back()->withInput()->withErrors(['email' => 'Tạm khóa do đăng nhập sai nhiều lần.']);
    }

    $user = User::where('email', $email)->first();
    if (!$user || !Hash::check($password, $user->password)) {
      RateLimiter::hit($key, $decaySeconds);
      session()->flash('toast_error', 'Email hoặc mật khẩu không đúng');
      return back()->withInput()->withErrors(['email' => 'Email hoặc mật khẩu không đúng']);
    }
    if ($user->status !== 'ACTIVE') {
      session()->flash('toast_error', 'Tài khoản đã bị khóa.');
      return back()->withInput()->withErrors(['email' => 'Tài khoản đã bị khóa']);
    }

    RateLimiter::clear($key);

    Auth::login($user, $remember);
    session()->regenerate();

    return redirect()
      ->intended(route('home'))
      ->with('toast_success', 'Đăng nhập thành công.');
  }

  public function logout(): void
  {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
  }
}
