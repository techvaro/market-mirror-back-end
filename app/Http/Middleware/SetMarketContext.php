<?php

namespace App\Http\Middleware;

use App\Models\Market;
use App\Support\MarketContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inspects the X-Market-Id header or market_id query/body parameter and
 * activates market scoping for the request so buyer-facing queries only
 * return shops and products that belong to the selected market.
 */
class SetMarketContext
{
    public function __construct(
        private readonly MarketContext $marketContext
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $marketId = $request->header('X-Market-Id')
            ?? $request->query('market_id')
            ?? $request->input('market_id');

        if ($marketId !== null && $marketId !== '') {
            if (!Market::whereKey($marketId)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The selected market does not exist.',
                ], 422);
            }

            $this->marketContext->set((int) $marketId);
            $request->attributes->set('market_id', (int) $marketId);
        }

        return $next($request);
    }
}
