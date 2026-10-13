<?php

namespace App\Http\Controllers\Api;

use App\Actions\Payments\CompleteFlutterwavePaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FlutterwaveWebhookRequest;
use App\Models\Payment;
use App\Models\Payout;
use App\Services\FlutterwaveService;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Handles asynchronous Flutterwave events. The route is intentionally outside
 * the auth middleware (Flutterwave cannot hold a user token) and is authenticated
 * instead by the VerifyFlutterwaveWebhook middleware via the FLW_SECRET_HASH.
 */
class FlutterwaveWebhookController extends Controller
{
    public function __construct(
        private readonly FlutterwaveService $flutterwave,
        private readonly CompleteFlutterwavePaymentAction $completePayment,
    ) {}

    public function handle(FlutterwaveWebhookRequest $request)
    {
        $event = (string) $request->validated('event');
        $data = (array) $request->input('data', []);

        if ($event === 'charge.completed' && ! empty($data['id'])) {
            try {
                // Never trust the webhook body for money movement: re-verify the
                // transaction directly with Flutterwave before touching the order.
                $transaction = $this->flutterwave->verify((string) $data['id']);
                $payment = Payment::where('reference', $transaction['tx_ref'] ?? '')->first();

                if ($payment) {
                    $this->completePayment->execute($payment, $transaction);
                }
            } catch (RuntimeException $exception) {
                Log::warning('Flutterwave webhook verification failed', ['error' => $exception->getMessage()]);

                return response()->json(['message' => 'Unable to verify event.'], 502);
            }
        }

        if (in_array($event, ['transfer.completed', 'transfer.failed'], true) && ! empty($data['reference'])) {
            Payout::where('transfer_reference', $data['reference'])->update([
                'status' => $event === 'transfer.completed' ? 'completed' : 'failed',
                'provider_response' => $data,
            ]);
        }

        return response()->json(['received' => true]);
    }
}
