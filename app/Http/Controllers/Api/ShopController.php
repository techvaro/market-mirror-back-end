<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreShopRequest;
use App\Http\Requests\Api\UpdateShopRequest;
use App\Http\Requests\Api\UploadShopImageRequest;
use App\Http\Resources\ReviewResource;
use App\Http\Resources\ShopImageResource;
use App\Http\Resources\ShopResource;
use App\Models\Shop;
use App\Models\ShopImage;
use App\Services\ShopService;
use App\Services\UploadService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShopController extends Controller
{
    public function __construct(
        private readonly ShopService $shopService,
        private readonly UploadService $uploadService
    ) {}

    public function index(Request $request)
    {
        $shops = $this->shopService->list(
            $request->only(['market', 'category', 'city', 'user_id', 'search', 'lat', 'lng', 'radius']),
            $request->get('per_page', 15)
        );

        return $this->paginated(ShopResource::collection($shops), $shops);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => new ShopResource($this->shopService->find($id)),
        ]);
    }

    public function store(StoreShopRequest $request)
    {
        $this->authorize('create', Shop::class);

        return response()->json([
            'success' => true,
            'data' => new ShopResource($this->shopService->create($request->sanitized(), $request->user()->id)),
            'message' => 'Shop created',
        ], 201);
    }

    public function update(UpdateShopRequest $request, $id)
    {
        $shop = $this->shopService->findForOwner($id);

        $this->authorize('update', $shop);

        return response()->json([
            'success' => true,
            'data' => new ShopResource($this->shopService->update($id, $request->sanitized())),
            'message' => 'Shop updated',
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $shop = $this->shopService->findForOwner($id);

        $this->authorize('delete', $shop);

        $this->shopService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'Shop deleted',
        ]);
    }

    public function reviews($id)
    {
        $reviews = $this->shopService->reviews($id);

        return $this->paginated(ReviewResource::collection($reviews), $reviews);
    }

    public function uploadImage(UploadShopImageRequest $request, $id)
    {
        $shop = $this->shopService->findForOwner($id);

        $this->authorize('update', $shop);

        $imageType = $request->validated('image_type');
        $sortOrder = $request->validated('sort_order') ?? 0;
        $existingImageQuery = $shop->shopImages()->where('image_type', $imageType);
        if ($imageType === 'gallery') {
            $existingImageQuery->where('sort_order', $sortOrder);
            if (! $existingImageQuery->exists() && $shop->shopImages()->where('image_type', 'gallery')->count() >= 4) {
                throw ValidationException::withMessages([
                    'image' => ['You can upload a maximum of four shop photos.'],
                ]);
            }
        }
        $existingImage = $existingImageQuery->first();
        if ($existingImage) {
            $this->uploadService->deleteByUrl($existingImage->image_url);
            $existingImage->delete();
        }

        $url = $this->uploadService->store($request->file('image'), 'shops');

        $image = ShopImage::create([
            'shop_id' => $shop->id,
            'image_url' => $url,
            'image_type' => $imageType,
            'sort_order' => $sortOrder,
        ]);

        return response()->json([
            'success' => true,
            'data' => new ShopImageResource($image),
            'message' => 'Shop image uploaded',
        ], 201);
    }

    public function removeImage(Request $request, $imageId)
    {
        $image = ShopImage::with('shop')->findOrFail($imageId);

        $this->authorize('update', $image->shop);

        $this->uploadService->deleteByUrl($image->image_url);

        $image->delete();

        return response()->json([
            'success' => true,
            'message' => 'Shop image removed',
        ]);
    }
}
