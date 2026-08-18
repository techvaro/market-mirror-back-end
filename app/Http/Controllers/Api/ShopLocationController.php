<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreShopLocationRequest;
use App\Http\Requests\Api\UpdateShopLocationRequest;
use App\Http\Requests\Api\UploadShopLocationPhotoRequest;
use App\Http\Resources\ShopLocationResource;
use App\Services\ShopLocationService;
use App\Services\UploadService;
use Illuminate\Http\Request;

class ShopLocationController extends Controller
{
    public function __construct(
        private readonly ShopLocationService $shopLocationService,
        private readonly UploadService $uploadService
    ) {}

    public function index(Request $request)
    {
        $locations = $this->shopLocationService->list(
            $request->only(['status', 'market', 'lat', 'lng', 'radius']),
            $request->get('per_page', 15)
        );

        return $this->paginated(ShopLocationResource::collection($locations), $locations);
    }

    public function store(StoreShopLocationRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new ShopLocationResource(
                $this->shopLocationService->create($request->sanitized(), $request->user()->mapperProfile)
            ),
            'message' => 'Location submitted',
        ], 201);
    }

    public function update(UpdateShopLocationRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new ShopLocationResource(
                $this->shopLocationService->update($id, $request->sanitized())
            ),
            'message' => 'Location updated',
        ]);
    }

    public function uploadPhoto(UploadShopLocationPhotoRequest $request, $id)
    {
        $location = $this->shopLocationService->find($id);

        $this->authorize('update', $location);

        $url = $this->uploadService->store($request->file('photo'), 'shop-locations');

        $photos = $this->uploadService->appendToJsonCollection($location->photos ?? [], $url);

        return response()->json([
            'success' => true,
            'data' => new ShopLocationResource($this->shopLocationService->update($id, ['photos' => $photos])),
            'message' => 'Photo uploaded',
        ], 201);
    }
}
