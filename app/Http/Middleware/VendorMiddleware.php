<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VendorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->role !== 'vendor') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Vendor access required.',
            ], 403);
        }

        return $next($request);
    }
}
