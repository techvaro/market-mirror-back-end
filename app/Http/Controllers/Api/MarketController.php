<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MarketResource;
use App\Services\MarketService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function __construct(
        private readonly MarketService $marketService
    ) {}

    public function index(Request $request)
    {
        $markets = $this->marketService->list($request->get('per_page', 15));

        return $this->paginated(MarketResource::collection($markets), $markets);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => new MarketResource($this->marketService->find($id)),
        ]);
    }
}
