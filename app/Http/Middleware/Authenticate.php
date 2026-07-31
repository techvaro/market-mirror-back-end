<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;
use Illuminate\Http\Request;

class Authenticate extends BaseAuthenticate
{
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }

    protected function unauthenticated($request, array $guards): void
    {
        abort(401, 'Unauthenticated. Please provide a valid JWT token.');
    }
}
