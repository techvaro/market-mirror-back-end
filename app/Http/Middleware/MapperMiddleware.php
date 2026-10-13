<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MapperMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'mapper') {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Mapper access required.',
            ], 403);
        }

        return $next($request);
    }
}
