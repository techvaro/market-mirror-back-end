<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Dispute;
use App\Models\Notification;
use App\Models\User;

class DisputeService
{
    public function list(array $filters, ?string $userRole = null, ?int $userId = null, int $perPage = 15)
    {
        $query = Dispute::with(['user', 'order.items.product.shop.user']);

        if ($userRole !== 'admin' && $userId) {
            $query->where('user_id', $userId);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data, int $userId): Dispute
    {
        $dispute = Dispute::create([
            ...$data,
            'user_id' => $userId,
            'status' => 'Under Review',
        ])->load('order.items.product.shop.user', 'user');

        $orderNumber = $dispute->order?->order_number ?? $dispute->order_id;
        User::where('role', 'admin')->each(function (User $admin) use ($orderNumber): void {
            Notification::create([
                'user_id' => $admin->id,
                'type' => 'dispute',
                'title' => 'New Dispute Filed',
                'message' => "A buyer filed a dispute for order {$orderNumber}.",
                'link' => '/disputes',
            ]);
        });

        return $dispute;
    }

    public function find(int $id): Dispute
    {
        return Dispute::with(['user', 'order.items.product.shop.user'])->findOrFail($id);
    }

    public function update(int $id, array $data): Dispute
    {
        $dispute = Dispute::findOrFail($id);
        $dispute->update($data);

        return $dispute;
    }

    public function contactVendors(Dispute $dispute, User $admin, string $message): int
    {
        $dispute->loadMissing('order.items.product.shop');
        $shops = $dispute->order?->items
            ->map(fn ($item) => $item->product?->shop)
            ->filter()
            ->unique('id') ?? collect();

        $sent = 0;
        foreach ($shops as $shop) {
            $conversation = ChatConversation::firstOrCreate([
                'user_id' => $dispute->user_id,
                'admin_id' => $admin->id,
                'shop_id' => $shop->id,
            ]);
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'sender' => 'admin',
                'text' => "Regarding dispute for order {$dispute->order?->order_number}: {$message}",
            ]);
            $conversation->touch();
            Notification::create([
                'user_id' => $shop->user_id,
                'type' => 'dispute',
                'title' => 'Admin Contacted You About a Dispute',
                'message' => 'An admin sent a message regarding a buyer dispute.',
                'link' => "/chat/{$conversation->id}",
            ]);
            $sent++;
        }

        return $sent;
    }
}
