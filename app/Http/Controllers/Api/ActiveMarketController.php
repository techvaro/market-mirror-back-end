<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreActiveMarketRequest;
use App\Http\Resources\MarketResource;
use App\Services\UserService;
use Illuminate\Http\Request;

class ActiveMarketController extends Controller
{
    public function __construct(
        private readonly UserService $userService
    ) {}

    public function show(Request $request)
    {
        $market = $this->userService->activeMarket($request->user());

        return response()->json([
            'success' => true,
            'data' => $market ? new MarketResource($market) : null,
        ]);
    }

    public function store(StoreActiveMarketRequest $request)
    {
        $user = $this->userService->setActiveMarket(
            $request->user(),
            $request->validated('market_id')
        );

        return response()->json([
            'success' => true,
            'data' => $user->activeMarket ? new MarketResource($user->activeMarket) : null,
            'message' => 'Active market updated',
        ]);
    }
}
