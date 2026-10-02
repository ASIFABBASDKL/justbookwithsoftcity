<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'moderator'])) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => false, 'message' => 'Admin access required.'], 403);
            }

            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
