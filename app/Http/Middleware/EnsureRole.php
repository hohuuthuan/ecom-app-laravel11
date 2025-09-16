<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
  public function handle(Request $request, Closure $next, string ...$roles): Response
  {
    $user = $request->user();
    if (!$user) {
      abort(401);
    }
    if (empty($roles)) {
      return $next($request);
    }

    $userRoleNames = $user->roles()->pluck('name')->map(fn($n) => strtoupper($n))->all();
    foreach ($roles as $r) {
      if (in_array(strtoupper($r), $userRoleNames, true)) {
        return $next($request);
      }
    }

    abort(403);
  }
}
