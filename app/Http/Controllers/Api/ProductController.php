<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->productService->list(
                $request->only(['shop_id', 'category', 'search', 'min_price', 'max_price', 'in_stock']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->productService->find($id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'sometimes|boolean',
            'color' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->productService->create($validated),
            'message' => 'Product created',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'category' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|boolean',
            'color' => 'sometimes|nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->productService->update($id, $validated),
            'message' => 'Product updated',
        ]);
    }

    public function destroy($id)
    {
        $this->productService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted',
        ]);
    }
}
