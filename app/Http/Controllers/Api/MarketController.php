<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketService;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    public function __construct(
        private readonly MarketService $marketService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->marketService->list($request->get('per_page', 15)),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->marketService->find($id),
        ]);
    }
}
