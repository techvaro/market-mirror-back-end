<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VendorApplicationService;
use Illuminate\Http\Request;

class VendorApplicationController extends Controller
{
    public function __construct(
        private readonly VendorApplicationService $vendorAppService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->vendorAppService->list(
                $request->only(['status']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'phone' => 'required|string',
            'category' => 'required|string',
            'market' => 'required|string',
            'description' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->vendorAppService->create($validated, $request->user()->id),
            'message' => 'Application submitted',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Pending Review,Approved,Rejected',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->vendorAppService->update($id, $validated),
            'message' => 'Application status updated',
        ]);
    }
}
