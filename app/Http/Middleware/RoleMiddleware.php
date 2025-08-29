<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
  public function handle(Request $request, Closure $next, ...$roles)
  {
    $user = $request->user();
    if (!$user) {
      abort(401);
    }

    $ok = $user->roles()->whereIn('name', $roles)->exists();

    if (!$ok) {
      return redirect()->route('home')->with('toast_error', 'Bạn không có quyền truy cập.');
    }

    return $next($request);
  }
}