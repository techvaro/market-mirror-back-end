<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authenticates inbound Flutterwave webhooks.
 *
 * Flutterwave includes the secret hash configured on the dashboard in the
 * `verif-hash` request header. The value must be kept server-side (FLW_SECRET_HASH)
 * and compared in constant time so an attacker cannot spoof transaction events.
 */
class VerifyFlutterwaveWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        $secretHash = (string) config('services.flutterwave.secret_hash');

        if ($secretHash === '') {
            Log::critical('Flutterwave webhook rejected: FLW_SECRET_HASH is not configured.');

            return response()->json(['message' => 'Webhook verification is not configured.'], 503);
        }

        $signature = (string) ($request->header('verif-hash')
            ?? $request->header('x-flutterwave-signature')
            ?? '');

        if ($signature === '' || ! hash_equals($secretHash, $signature)) {
            Log::warning('Flutterwave webhook rejected: invalid signature.', [
                'ip' => $request->ip(),
                'event' => $request->input('event'),
            ]);

            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        return $next($request);
    }
}
