<?php

namespace App\Actions\Payments;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Reconciles a Flutterwave transaction against a local payment and, when the
 * amount/currency/reference all match, marks the payment complete and confirms
 * the order atomically. Both the redirect verification and the webhook path use
 * this action so the trust rules live in exactly one place.
 */
class CompleteFlutterwavePaymentAction
{
    public function execute(Payment $payment, array $transaction): void
    {
        $valid = ($transaction['status'] ?? null) === 'successful'
            && ($transaction['currency'] ?? null) === $payment->currency
            && (float) ($transaction['amount'] ?? 0) >= (float) $payment->amount
            && ($transaction['tx_ref'] ?? null) === $payment->reference;

        if (! $valid) {
            $payment->update(['status' => 'failed', 'metadata' => ['verification' => $transaction]]);

            throw new RuntimeException('Payment details did not match the order.');
        }

        DB::transaction(function () use ($payment, $transaction) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'completed') {
                return;
            }

            $locked->update([
                'status' => 'completed',
                'provider_transaction_id' => (string) $transaction['id'],
                'metadata' => ['verification' => $transaction],
            ]);

            $order = Order::lockForUpdate()->findOrFail($locked->order_id);
            $order->update(['status' => 'confirmed', 'payment_method' => 'flutterwave']);

            CartItem::where('user_id', $order->user_id)
                ->whereIn('product_id', $order->items()->pluck('product_id'))
                ->delete();
        });
    }
}
