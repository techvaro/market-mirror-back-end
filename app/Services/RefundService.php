<?php

namespace App\Services;

use App\Models\Dispute;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class RefundService
{
    public function __construct(private readonly FlutterwaveService $flutterwave) {}

    public function refundOrder(Order $order, ?Dispute $dispute = null): ?Refund
    {
        $existing = Refund::where('order_id', $order->id)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->latest()
            ->first();
        if ($existing) {
            return $existing;
        }

        $payment = Payment::where('order_id', $order->id)->where('status', 'completed')->latest()->first();
        if (! $payment) {
            return null;
        }

        $buyer = $order->user;
        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'dispute_id' => $dispute?->id,
            'user_id' => $buyer->id,
            'amount' => (int) $payment->amount,
            'currency' => $payment->currency,
            'bank_code' => (string) $buyer->bank_code,
            'bank_name' => (string) $buyer->bank_name,
            'account_number' => (string) $buyer->bank_account_number,
            'account_name' => (string) $buyer->bank_account_name,
            'status' => 'pending',
            'transfer_reference' => 'REFUND-'.strtoupper(Str::random(20)),
        ]);

        if (! $buyer->bank_code || ! $buyer->bank_account_number || ! $buyer->bank_account_name) {
            Notification::create([
                'user_id' => $buyer->id,
                'type' => 'payment',
                'title' => 'Refund Account Required',
                'message' => 'Your order was cancelled. Add and verify a refund bank account in your profile so we can send your refund.',
                'link' => '/profile',
            ]);

            return $refund;
        }

        try {
            $refund->update(['status' => 'processing']);
            $response = $this->flutterwave->createRefundTransfer($refund);
            DB::transaction(function () use ($refund, $payment, $response) {
                $refund->update(['status' => 'completed', 'provider_response' => $response]);
                $payment->update(['status' => 'refunded']);
            });
            Notification::create([
                'user_id' => $buyer->id,
                'type' => 'payment',
                'title' => 'Refund Sent',
                'message' => "Your refund for order {$order->order_number} has been sent to your saved bank account.",
                'link' => "/orders/{$order->id}",
            ]);
        } catch (Throwable $exception) {
            $refund->update(['status' => 'failed', 'provider_response' => ['error' => $exception->getMessage()]]);
            Notification::create([
                'user_id' => $buyer->id,
                'type' => 'payment',
                'title' => 'Refund Processing Delayed',
                'message' => "Your order {$order->order_number} was cancelled, but the refund needs to be retried by support.",
                'link' => "/orders/{$order->id}",
            ]);
        }

        return $refund->fresh();
    }
}
