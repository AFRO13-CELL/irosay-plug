<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enforces permissions on the BACKEND, per the spec's explicit rule:
 * "Permissions must be enforced on the backend. Do not rely only on
 * hiding buttons." Apply as middleware('permission:slug.here') on any
 * route a role might not have — a user without the slug gets a 403,
 * regardless of what the UI happens to show them.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasPermission($permission)) {
            abort(403, "You don't have permission to do that ({$permission}).");
        }

        return $next($request);
    }
}
