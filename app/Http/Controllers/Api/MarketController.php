<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreMarketRequest;
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

    public function adminIndex(Request $request)
    {
        $markets = $this->marketService->adminList($request->get('per_page', 15));

        return $this->paginated(MarketResource::collection($markets), $markets);
    }

    public function store(StoreMarketRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new MarketResource($this->marketService->create($request->sanitized())),
            'message' => 'Market created',
        ], 201);
    }

    public function update(StoreMarketRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new MarketResource($this->marketService->update($id, $request->sanitized())),
            'message' => 'Market updated',
        ]);
    }

    public function toggleActive(Request $request, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);

        return response()->json([
            'success' => true,
            'data' => new MarketResource($this->marketService->setActive($id, (bool) $request->is_active)),
            'message' => 'Market updated',
        ]);
    }

    public function destroy($id)
    {
        $this->marketService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Market deleted',
        ]);
    }
}
