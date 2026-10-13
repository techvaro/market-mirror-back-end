<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Notification;

class MessageService
{
    public function list(int $userId, int $perPage = 15, ?string $userRole = null)
    {
        $sender = ($userRole === 'vendor') ? 'user' : 'vendor';

        $query = ChatConversation::query();
        if ($userRole === 'admin') {
            $query->where('admin_id', $userId);
        } elseif ($userRole === 'vendor') {
            $query->whereHas('shop', fn ($s) => $s->where('user_id', $userId));
        } else {
            $query->where('admin_id', null)->where('user_id', $userId);
        }

        return $query->with(['shop.user', 'shop', 'user', 'admin', 'messages' => function ($q) {
            $q->latest()->limit(1);
        }])
            ->withCount(['messages as unread_count' => function ($q) use ($sender) {
                $q->whereIn('sender', $sender === 'user' ? ['user', 'admin'] : [$sender])->where('read', false);
            }])
            ->latest()
            ->paginate($perPage);
    }

    public function find(int $id, int $userId, ?string $userRole = null): ChatConversation
    {
        $conversationQuery = ChatConversation::where('id', $id);
        if ($userRole === 'admin') {
            $conversationQuery->where('admin_id', $userId);
        } elseif ($userRole === 'vendor') {
            $conversationQuery->whereHas('shop', fn ($s) => $s->where('user_id', $userId));
        } else {
            $conversationQuery->where('admin_id', null)->where('user_id', $userId);
        }

        $conversation = $conversationQuery
            ->with(['messages', 'shop.user', 'shop', 'user', 'admin'])
            ->firstOrFail();

        $sender = ($userRole === 'vendor') ? 'user' : 'vendor';

        ChatMessage::where('conversation_id', $id)
            ->whereIn('sender', $sender === 'user' ? ['user', 'admin'] : [$sender])
            ->where('read', false)
            ->update(['read' => true]);

        return $conversation;
    }

    public function findOrCreateConversation(int $userId, int $shopId): ChatConversation
    {
        return ChatConversation::firstOrCreate([
            'user_id' => $userId,
            'shop_id' => $shopId,
        ]);
    }

    public function create(array $data, int $userId): ChatMessage
    {
        $conversation = ChatConversation::where('id', $data['conversation_id'])
            ->where('user_id', $userId)
            ->firstOrFail();

        return $this->storeMessage($conversation, 'user', $data, $conversation->shop->user_id);
    }

    public function createAsVendor(array $data, int $userId): ChatMessage
    {
        $conversation = ChatConversation::where('id', $data['conversation_id'])
            ->whereHas('shop', fn ($s) => $s->where('user_id', $userId))
            ->firstOrFail();

        return $this->storeMessage($conversation, 'vendor', $data, $conversation->user_id);
    }

    public function createAsAdmin(array $data, int $userId): ChatMessage
    {
        $conversation = ChatConversation::where('id', $data['conversation_id'])
            ->where('admin_id', $userId)
            ->firstOrFail();

        return $this->storeMessage($conversation, 'admin', $data, $conversation->shop->user_id);
    }

    private function storeMessage(ChatConversation $conversation, string $sender, array $data, int $notifyUserId): ChatMessage
    {
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'sender' => $sender,
            'text' => $data['text'],
            'image_url' => $data['image_url'] ?? null,
        ]);

        $conversation->touch();

        Notification::create([
            'user_id' => $notifyUserId,
            'type' => 'chat',
            'title' => 'New Message',
            'message' => substr($data['text'], 0, 100),
            'link' => "/chat/{$conversation->id}",
        ]);

        return $message;
    }
}
