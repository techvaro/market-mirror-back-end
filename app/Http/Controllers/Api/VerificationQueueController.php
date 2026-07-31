<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VerificationQueueService;
use Illuminate\Http\Request;

class VerificationQueueController extends Controller
{
    public function __construct(
        private readonly VerificationQueueService $verificationService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->verificationService->list(
                $request->only(['status']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,approved,rejected,revision_requested',
            'notes' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->verificationService->update($id, $validated),
            'message' => 'Verification updated',
        ]);
    }
}
