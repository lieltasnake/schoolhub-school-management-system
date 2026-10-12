<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $allowedRoles = collect($roles)
            ->flatMap(fn (string $role) => explode('|', $role))
            ->map(fn (string $role) => strtolower(trim($role)))
            ->filter()
            ->all();

        if ($allowedRoles === [] || ! $request->user()->hasAnyRole($allowedRoles)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
