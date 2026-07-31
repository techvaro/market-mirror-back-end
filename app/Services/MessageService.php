<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;

class MessageService
{
    public function list(int $userId, int $perPage = 15)
    {
        return ChatConversation::where('user_id', $userId)
            ->with(['shop', 'messages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->latest()
            ->paginate($perPage);
    }

    public function create(array $data): ChatMessage
    {
        return ChatMessage::create([
            'conversation_id' => $data['conversation_id'],
            'sender' => 'user',
            'text' => $data['text'],
            'image_url' => $data['image_url'] ?? null,
        ]);
    }

    public function find(int $id): ChatConversation
    {
        $conversation = ChatConversation::with(['messages', 'shop'])->findOrFail($id);

        ChatMessage::where('conversation_id', $id)
            ->where('sender', '!=', 'user')
            ->where('read', false)
            ->update(['read' => true]);

        return $conversation;
    }
}
