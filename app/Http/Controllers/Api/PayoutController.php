<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PayoutService;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->payoutService->list(
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string',
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->payoutService->create($validated, $request->user()->id),
            'message' => 'Payout request submitted',
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->payoutService->find($id),
        ]);
    }
}
