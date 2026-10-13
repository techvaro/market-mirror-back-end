<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\CompleteFlutterwavePaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\InitializeFlutterwavePaymentRequest;
use App\Http\Requests\Api\VerifyFlutterwavePaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use App\Services\FlutterwaveService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FlutterwaveController extends Controller
{
    public function __construct(
        private readonly FlutterwaveService $flutterwave,
        private readonly CompleteFlutterwavePaymentAction $completePayment,
    ) {}

    public function initialize(InitializeFlutterwavePaymentRequest $request)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($request->validated('order_id'));

        if (! in_array($order->status, ['payment_pending', 'cancelled'], true)) {
            return response()->json(['message' => 'This order is no longer payable.'], 422);
        }

        try {
            return response()->json(['success' => true, 'data' => $this->flutterwave->initialize($order, $request->sanitized())]);
        } catch (RuntimeException $exception) {
            Log::error('Flutterwave initialization failed', ['order_id' => $order->id, 'error' => $exception->getMessage()]);

            return response()->json(['message' => $exception->getMessage()], 502);
        }
    }

    public function verify(VerifyFlutterwavePaymentRequest $request)
    {
        $data = $request->validated();

        try {
            $transaction = $this->flutterwave->verify($data['transaction_id']);

            // Scope the lookup to the caller so one user cannot finalize another
            // user's payment. Admins may reconcile any transaction.
            $query = Payment::where('reference', $transaction['tx_ref'] ?? '');
            if ($request->user()->role !== 'admin') {
                $query->where('user_id', $request->user()->id);
            }
            $payment = $query->firstOrFail();

            $this->completePayment->execute($payment, $transaction);

            return response()->json(['success' => true, 'data' => ['status' => 'successful', 'order_id' => $payment->order_id]]);
        } catch (ModelNotFoundException) {
            // Do not reveal whether the transaction exists for another user.
            return response()->json(['success' => false, 'message' => 'Payment not found.'], 404);
        } catch (RuntimeException $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
