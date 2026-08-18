<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function index(Request $request)
    {
        $payments = $this->paymentService->list(
            [],
            $request->user()->role,
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(PaymentResource::collection($payments), $payments);
    }

    public function store(StorePaymentRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new PaymentResource(
                $this->paymentService->create($request->sanitized(), $request->user()->id)
            ),
            'message' => 'Payment recorded',
        ], 201);
    }
}
