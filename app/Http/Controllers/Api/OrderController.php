<?php

namespace App\Http\Controllers\Api;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use App\Http\Requests\Api\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
        private readonly CreateOrderAction $createOrderAction
    ) {}

    public function index(Request $request)
    {
        $orders = $this->orderService->list(
            $request->only(['status']),
            $request->user()->role,
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(OrderResource::collection($orders), $orders);
    }

    public function show(Request $request, $id)
    {
        $order = $this->orderService->find($id);

        $this->authorize('view', $order);

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $order = $this->createOrderAction->execute(
            $request->sanitized(),
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'data' => new OrderResource($order),
            'message' => 'Order placed successfully',
        ], 201);
    }

    public function update(UpdateOrderRequest $request, $id)
    {
        $order = $this->orderService->find($id);

        $this->authorize('update', $order);

        $updated = $this->orderService->update($id, $request->sanitized());

        return response()->json([
            'success' => true,
            'data' => new OrderResource($updated->load(['user', 'items.product'])),
            'message' => 'Order updated',
        ]);
    }
}
