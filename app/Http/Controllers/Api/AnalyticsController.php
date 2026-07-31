<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsService $analyticsService
    ) {}

    public function vendor(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->vendor($request->user()->id),
        ]);
    }

    public function vendorSales(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->analyticsService->vendorSales($request->user()->id),
        ]);
    }
}
