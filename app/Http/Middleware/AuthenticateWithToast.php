<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateWithToast
{
    /**
     * Hỗ trợ nhiều guard giống middleware 'auth' của Laravel.
     * Dùng: ->middleware('auth.toast') hoặc 'auth.toast:web'
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return $next($request);
            }
        }

        // Chưa đăng nhập -> chuyển về login + toast
        return redirect()
            ->route('login.form')
            ->with('toast_error', 'Bạn cần phải đăng nhập.');
    }
}
