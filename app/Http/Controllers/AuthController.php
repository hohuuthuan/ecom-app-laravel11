<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Models\User;
use App\Models\Role;
use Throwable;

class AuthController extends Controller
{
  public function __construct(
    private readonly AuthService $authService,
  ) {}

  public function register(RegisterRequest $request): RedirectResponse
  {
    try {
      if (User::where('email', $request->email)->exists()) {
        return back()
          ->withInput()
          ->with('toast_warning', 'Email đã được sử dụng')
          ->withErrors(['email' => 'Email đã được sử dụng']);
      }

      $ok = $this->authService->register($request->validated());
      if (!$ok) {
        return back()
          ->withInput()
          ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
      }
    } catch (Throwable $e) {
      return back()
        ->withInput()
        ->with('toast_error', 'Có lỗi xảy ra, vui lòng thử lại sau');
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
      ->with('toast_success', 'Đăng xuất thành công.');
  }

  public function redirectToGoogle(): RedirectResponse
  {
    return Socialite::driver('google')
      ->with(['prompt' => 'select_account'])
      ->redirect();
  }

  public function handleGoogleCallback(Request $request): RedirectResponse
  {
    try {
      $googleUser = Socialite::driver('google')->stateless()->user();

      $email = $googleUser->getEmail();
      if (!$email) {
        return redirect()
          ->route('login.form')
          ->with('toast_error', 'Tài khoản Google không có email, không thể đăng nhập.');
      }

      /** @var \App\Models\User|null $user */
      $user = User::where('google_id', $googleUser->getId())
        ->orWhere('email', $email)
        ->first();

      if ($user) {
        if (!$user->google_id) {
          $user->google_id = $googleUser->getId();
          $user->save();
        }
      } else {
        $user = User::create([
          'id'                => (string) Str::uuid(),
          'name'              => $googleUser->getName() ?: ($googleUser->getNickname() ?: $email),
          'email'             => $email,
          'google_id'         => $googleUser->getId(),
          'password'          => Hash::make(Str::random(32)),
          'email_verified_at' => now(),
          'status'            => 'ACTIVE',
        ]);

        $roleId = Role::where('name', 'Customer')->value('id');
        if ($roleId !== null) {
          $user->roles()->attach($roleId);
        }
      }

      Auth::login($user, true);

      return redirect()
        ->intended(route('home'))
        ->with('toast_success', 'Đăng nhập bằng Google thành công.');
    } catch (\Throwable $e) {
      Log::error('Google login error', [
        'message' => $e->getMessage(),
      ]);

      return redirect()
        ->route('login.form')
        ->with('toast_error', 'Không thể đăng nhập bằng Google, vui lòng thử lại sau.');
    }
  }

  public function showLinkRequestForm(): \Illuminate\View\View
  {
    return view('auth.passwords.email');
  }

  public function sendResetLinkEmail(Request $request): \Illuminate\Http\RedirectResponse
  {
    $data = $request->validate([
      'email' => ['required', 'email'],
    ]);

    $status = Password::sendResetLink([
      'email' => $data['email'],
    ]);

    if ($status === Password::RESET_LINK_SENT) {
      return back()->with('status', __($status));
    }

    return back()
      ->withInput($request->only('email'))
      ->withErrors(['email' => __($status)]);
  }

  public function showResetForm(Request $request, string $token): \Illuminate\View\View
  {
    return view('auth.passwords.reset', [
      'token' => $token,
      'email' => $request->query('email'),
    ]);
  }

  public function reset(Request $request): \Illuminate\Http\RedirectResponse
  {
    $data = $request->validate([
      'token'    => ['required'],
      'email'    => ['required', 'email'],
      'password' => ['required', 'confirmed', 'min:6'],
    ]);

    $status = Password::reset(
      $data,
      function (User $user, string $password): void {
        $user->forceFill([
          'password'       => Hash::make($password),
          'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
      }
    );
    if ($status === Password::PASSWORD_RESET) {
      return redirect()
        ->route('login')
        ->with('toast_success', __('Mật khẩu của bạn đã được đặt lại, vui lòng đăng nhập lại.'));
    }

    return back()
      ->withInput($request->only('email'))
      ->withErrors(['email' => __($status)]);
  }
}
