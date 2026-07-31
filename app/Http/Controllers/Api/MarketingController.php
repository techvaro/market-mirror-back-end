<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MarketingService;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function __construct(
        private readonly MarketingService $marketingService
    ) {}

    // ─── Coupons ──────────────────────────────────────────────────
    public function coupons(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->marketingService->listCoupons(
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function storeCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:coupons,code',
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_until' => 'required|date',
            'usage_limit' => 'nullable|integer|min:1',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->marketingService->createCoupon($validated, $request->user()->id),
            'message' => 'Coupon created',
        ], 201);
    }

    public function updateCoupon(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:255|unique:coupons,code,' . $id,
            'title' => 'nullable|string',
            'description' => 'nullable|string',
            'discount_type' => 'sometimes|in:percentage,fixed',
            'discount_value' => 'sometimes|numeric|min:0',
            'valid_until' => 'sometimes|date',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->marketingService->updateCoupon($id, $validated),
            'message' => 'Coupon updated',
        ]);
    }

    public function destroyCoupon($id)
    {
        $this->marketingService->deleteCoupon($id);

        return response()->json([
            'success' => true,
            'message' => 'Coupon deleted',
        ]);
    }

    // ─── Flash Sales ──────────────────────────────────────────────
    public function flashSales(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->marketingService->listFlashSales(
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function storeFlashSale(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name' => 'nullable|string',
            'original_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'stock_count' => 'nullable|integer|min:1',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->marketingService->createFlashSale($validated, $request->user()->id),
            'message' => 'Flash sale created',
        ], 201);
    }

    public function updateFlashSale(Request $request, $id)
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'name' => 'nullable|string',
            'original_price' => 'sometimes|numeric|min:0',
            'sale_price' => 'sometimes|numeric|min:0',
            'start_time' => 'sometimes|date',
            'end_time' => 'sometimes|date|after:start_time',
            'stock_count' => 'nullable|integer|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->marketingService->updateFlashSale($id, $validated),
            'message' => 'Flash sale updated',
        ]);
    }

    public function destroyFlashSale($id)
    {
        $this->marketingService->deleteFlashSale($id);

        return response()->json([
            'success' => true,
            'message' => 'Flash sale deleted',
        ]);
    }
}
