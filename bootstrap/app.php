<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\MapperMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SetMarketContext;
use App\Http\Middleware\VendorMiddleware;
use App\Http\Middleware\VerifyFlutterwaveWebhook;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'admin' => AdminMiddleware::class,
            'mapper' => MapperMiddleware::class,
            'vendor' => VendorMiddleware::class,
            'market' => SetMarketContext::class,
            'flutterwave.webhook' => VerifyFlutterwaveWebhook::class,
        ]);

        $middleware->redirectGuestsTo(function ($request) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        });

        $middleware->redirectUsersTo('/');

        $middleware->validateCsrfTokens(except: ['api/*']);

        $middleware->statefulApi();
        $middleware->api(prepend: [
            HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
