<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            'data' => $this->cartService->list($request->user()->id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant' => 'nullable|string',
            'quantity' => 'required|integer|min:1',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->cartService->add($validated, $request->user()->id),
            'message' => 'Added to cart',
        ], 201);
    }

    public function update(Request $request, $productId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'variant' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->cartService->updateQuantity(
                $request->user()->id,
                $productId,
                $validated
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
