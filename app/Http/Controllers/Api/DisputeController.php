<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DisputeService;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->disputeService->list(
                $request->only(['status']),
                $request->user()->role,
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
            'description' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->disputeService->create($validated, $request->user()->id),
            'message' => 'Dispute filed',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:Under Review,Resolved,Closed',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->disputeService->update($id, $validated),
            'message' => 'Dispute updated',
        ]);
    }
}
