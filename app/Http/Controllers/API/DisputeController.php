<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $disputes = $request->user()
            ->disputes()
            ->with('order')
            ->latest()
            ->paginate(20);

        return response()->json($disputes);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
        ]);

        $order = $request->user()->orders()->findOrFail($validated['order_id']);

        $dispute = $request->user()->disputes()->create([
            'order_id' => $validated['order_id'],
            'reason' => $validated['reason'],
            'description' => $validated['description'],
            'status' => 'Under Review',
        ]);

        return response()->json($dispute, 201);
    }
}
