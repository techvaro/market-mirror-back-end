<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreCouponRequest;
use App\Http\Requests\Api\StoreFlashSaleRequest;
use App\Http\Requests\Api\UpdateCouponRequest;
use App\Http\Requests\Api\UpdateFlashSaleRequest;
use App\Http\Resources\CouponResource;
use App\Http\Resources\FlashSaleResource;
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
        $coupons = $this->marketingService->listCoupons(
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(CouponResource::collection($coupons), $coupons);
    }

    public function storeCoupon(StoreCouponRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new CouponResource(
                $this->marketingService->createCoupon($request->sanitized(), $request->user()->id)
            ),
            'message' => 'Coupon created',
        ], 201);
    }

    public function updateCoupon(UpdateCouponRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new CouponResource($this->marketingService->updateCoupon($id, $request->sanitized())),
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
        $sales = $this->marketingService->listFlashSales(
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(FlashSaleResource::collection($sales), $sales);
    }

    public function storeFlashSale(StoreFlashSaleRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new FlashSaleResource(
                $this->marketingService->createFlashSale($request->sanitized(), $request->user()->id)
            ),
            'message' => 'Flash sale created',
        ], 201);
    }

    public function updateFlashSale(UpdateFlashSaleRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new FlashSaleResource($this->marketingService->updateFlashSale($id, $request->sanitized())),
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
