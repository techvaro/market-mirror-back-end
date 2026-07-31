<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VendorRegistrationService;
use Illuminate\Http\Request;

class VendorRegistrationController extends Controller
{
    public function __construct(
        private readonly VendorRegistrationService $vendorRegService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->vendorRegService->list(
                $request->only(['status']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => 'required|string',
            'business_category' => 'required|string',
            'market' => 'required|string',
            'photos' => 'nullable|array',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->vendorRegService->create($validated, $request->user()->mapperProfile),
            'message' => 'Registration submitted',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'approval_status' => 'required|in:pending_review,approved,rejected,changes_requested',
            'notes' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->vendorRegService->update($id, $validated),
            'message' => 'Registration updated',
        ]);
    }
}
