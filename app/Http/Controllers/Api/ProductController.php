<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Requests\Api\UpdateProductRequest;
use App\Http\Requests\Api\UploadProductImageRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use App\Services\UploadService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly UploadService $uploadService
    ) {}

    public function index(Request $request)
    {
        $products = $this->productService->list(
            $request->only(['shop_id', 'category', 'search', 'min_price', 'max_price', 'in_stock', 'sort']),
            $request->get('per_page', 15)
        );

        return $this->paginated(ProductResource::collection($products), $products);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => new ProductResource($this->productService->find($id)),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($this->productService->create($request->sanitized())),
            'message' => 'Product created',
        ], 201);
    }

    public function update(UpdateProductRequest $request, $id)
    {
        $product = $this->productService->findForOwner($id);

        $this->authorize('update', $product);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($this->productService->update($id, $request->sanitized())),
            'message' => 'Product updated',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $product = $this->productService->findForOwner($id);

        $this->authorize('delete', $product);

        $this->uploadService->deleteByUrl($product->image_url);

        $this->productService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted',
        ]);
    }

    public function uploadImage(UploadProductImageRequest $request, $id)
    {
        $product = $this->productService->findForOwner($id);

        $this->authorize('update', $product);

        $url = $this->uploadService->store($request->file('image'), 'products');

        $this->uploadService->deleteByUrl($product->image_url);

        $this->productService->update($id, ['image_url' => $url]);

        return response()->json([
            'success' => true,
            'data' => new ProductResource($this->productService->find($id)),
            'message' => 'Product image uploaded',
        ]);
    }

    public function removeImage(Request $request, $id)
    {
        $product = $this->productService->findForOwner($id);

        $this->authorize('update', $product);

        $this->uploadService->deleteByUrl($product->image_url);

        $this->productService->update($id, ['image_url' => null]);

        return response()->json([
            'success' => true,
            'message' => 'Product image removed',
        ]);
    }
}
