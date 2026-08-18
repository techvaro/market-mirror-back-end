<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AddToCartRequest;
use App\Http\Requests\Api\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => CartItemResource::collection($this->cartService->list($request->user()->id)),
        ]);
    }

    public function store(AddToCartRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new CartItemResource($this->cartService->add($request->sanitized(), $request->user()->id)),
            'message' => 'Added to cart',
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, $productId)
    {
        return response()->json([
            'success' => true,
            'data' => new CartItemResource(
                $this->cartService->updateQuantity($request->user()->id, $productId, $request->sanitized())
            ),
            'message' => 'Cart updated',
        ]);
    }

    public function destroy(Request $request, $productId)
    {
        $this->cartService->remove(
            $request->user()->id,
            $productId,
            $request->get('variant')
        );

        return response()->json([
            'success' => true,
            'message' => 'Removed from cart',
        ]);
    }

    public function clear(Request $request)
    {
        $this->cartService->clear($request->user()->id);

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }
}
