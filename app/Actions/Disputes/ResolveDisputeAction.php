<?php

namespace App\Actions\Disputes;

use App\Models\Dispute;
use App\Models\Order;
use App\Models\User;
use App\Services\RefundService;
use Illuminate\Validation\ValidationException;

/**
 * Resolves a dispute through the admin resolution workflow:
 * - refund_buyer: mark the order cancelled, dispute refunded.
 * - release_to_vendor: mark the order delivered/released, dispute resolved.
 * - escalate: hand the dispute to a higher review queue.
 */
class ResolveDisputeAction
{
    public const ACTIONS = ['refund_buyer', 'release_to_vendor', 'escalate'];

    public function __construct(private readonly RefundService $refundService) {}

    public function execute(Dispute $dispute, string $action, User $admin, ?string $note = null): Dispute
    {
        if (! in_array($action, self::ACTIONS, true)) {
            throw ValidationException::withMessages([
                'action' => ['Invalid resolution action.'],
            ]);
        }

        $order = $dispute->order;

        match ($action) {
            'refund_buyer' => $this->refundBuyer($dispute, $order),
            'release_to_vendor' => $this->releaseToVendor($dispute, $order),
            'escalate' => $this->escalate($dispute),
        };

        $dispute->update([
            'resolved_by' => $admin->id,
            'resolution_note' => $note,
        ]);

        return $dispute->load('order', 'user');
    }

    private function refundBuyer(Dispute $dispute, ?Order $order): void
    {
        $dispute->update(['status' => 'Refunded']);

        if ($order && $order->status !== 'cancelled') {
            $order->update(['status' => 'cancelled']);
        }

        if ($order) {
            $this->refundService->refundOrder($order->load('user'), $dispute);
        }
    }

    private function releaseToVendor(Dispute $dispute, ?Order $order): void
    {
        $dispute->update(['status' => 'Released']);

        if ($order && $order->status === 'cancelled') {
            $order->update(['status' => 'delivered']);
        }
    }

    private function escalate(Dispute $dispute): void
    {
        $dispute->update(['status' => 'Escalated']);
    }
}
