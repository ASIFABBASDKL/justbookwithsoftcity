<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSeller
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user?->is_seller || ! $user->sellerProfile) {
            return redirect()
                ->route('web.seller.onboard')
                ->with('info', 'Create a seller profile to open the seller dashboard.');
        }

        return $next($request);
    }
}
