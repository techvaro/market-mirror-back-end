<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreVendorApplicationRequest;
use App\Http\Requests\Api\UpdateVendorApplicationRequest;
use App\Http\Resources\VendorApplicationResource;
use App\Services\VendorApplicationService;
use Illuminate\Http\Request;

class VendorApplicationController extends Controller
{
    public function __construct(
        private readonly VendorApplicationService $vendorAppService
    ) {}

    public function index(Request $request)
    {
        $applications = $this->vendorAppService->list(
            $request->only(['status']),
            $request->get('per_page', 15)
        );

        return $this->paginated(VendorApplicationResource::collection($applications), $applications);
    }

    public function store(StoreVendorApplicationRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new VendorApplicationResource(
                $this->vendorAppService->create($request->sanitized(), $request->user()->id)
            ),
            'message' => 'Application submitted',
        ], 201);
    }

    public function update(UpdateVendorApplicationRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new VendorApplicationResource(
                $this->vendorAppService->update($id, $request->sanitized())
            ),
            'message' => 'Application status updated',
        ]);
    }
}
