<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\VendorApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $applications = $request->user()
            ->vendorApplications()
            ->latest()
            ->paginate(20);

        return response()->json($applications);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shop_name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'market' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ]);

        $application = $request->user()->vendorApplications()->create([
            ...$validated,
            'status' => 'Pending Review',
        ]);

        return response()->json($application, 201);
    }

    public function show(Request $request, VendorApplication $vendorApplication): JsonResponse
    {
        if ($vendorApplication->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        return response()->json($vendorApplication);
    }
}
