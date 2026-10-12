<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $branchId = $request->route('branch_id')
            ?? $request->route('branch')
            ?? $request->input('branch_id');

        if (! $request->user()->canAccessBranch($branchId === null ? null : (int) $branchId)) {
            abort(403, 'This branch is not assigned to your account.');
        }

        return $next($request);
    }
}
