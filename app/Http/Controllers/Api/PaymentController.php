<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->paymentService->list(
                [],
                $request->user()->role,
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string',
            'method' => 'required|string',
            'reference' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->paymentService->create($validated, $request->user()->id),
            'message' => 'Payment recorded',
        ], 201);
    }
}
