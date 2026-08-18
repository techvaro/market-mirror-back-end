<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreVendorRegistrationRequest;
use App\Http\Requests\Api\UpdateVendorRegistrationRequest;
use App\Http\Requests\Api\UploadVendorRegistrationPhotoRequest;
use App\Http\Resources\VendorRegistrationResource;
use App\Services\UploadService;
use App\Services\VendorRegistrationService;
use Illuminate\Http\Request;

class VendorRegistrationController extends Controller
{
    public function __construct(
        private readonly VendorRegistrationService $vendorRegService,
        private readonly UploadService $uploadService
    ) {}

    public function index(Request $request)
    {
        $registrations = $this->vendorRegService->list(
            $request->only(['status']),
            $request->get('per_page', 15)
        );

        return $this->paginated(VendorRegistrationResource::collection($registrations), $registrations);
    }

    public function store(StoreVendorRegistrationRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new VendorRegistrationResource(
                $this->vendorRegService->create($request->sanitized(), $request->user()->mapperProfile)
            ),
            'message' => 'Registration submitted',
        ], 201);
    }

    public function update(UpdateVendorRegistrationRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new VendorRegistrationResource(
                $this->vendorRegService->update($id, $request->sanitized())
            ),
            'message' => 'Registration updated',
        ]);
    }

    public function uploadPhoto(UploadVendorRegistrationPhotoRequest $request, $id)
    {
        $registration = $this->vendorRegService->find($id);

        $this->authorize('update', $registration);

        $url = $this->uploadService->store($request->file('photo'), 'vendor-registrations');

        $photos = $this->uploadService->appendToJsonCollection($registration->photos ?? [], $url);

        return response()->json([
            'success' => true,
            'data' => new VendorRegistrationResource($this->vendorRegService->update($id, ['photos' => $photos])),
            'message' => 'Photo uploaded',
        ], 201);
    }
}
