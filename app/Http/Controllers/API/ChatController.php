<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Shop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function conversations(Request $request): JsonResponse
    {
        $conversations = $request->user()
            ->chatConversations()
            ->with('shop')
            ->withCount('messages')
            ->latest()
            ->paginate(20);

        return response()->json($conversations);
    }

    public function messages(Request $request, ChatConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $conversation->messages()->where('sender', 'vendor')->where('read', false)->update(['read' => true]);

        $messages = $conversation->messages()->latest()->limit(50)->get()->reverse();

        return response()->json([
            'conversation' => $conversation->load('shop'),
            'messages' => $messages,
        ]);
    }

    public function sendMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        if ($conversation->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Not authorized'], 403);
        }

        $validated = $request->validate([
            'text' => 'required|string|max:2000',
            'image_url' => 'nullable|string|max:500',
        ]);

        $message = $conversation->messages()->create([
            'sender' => 'user',
            'text' => $validated['text'],
            'image_url' => $validated['image_url'] ?? null,
            'read' => false,
        ]);

        return response()->json($message, 201);
    }

    public function startConversation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shop_id' => 'required|exists:shops,id',
        ]);

        $conversation = ChatConversation::firstOrCreate([
            'user_id' => $request->user()->id,
            'shop_id' => $validated['shop_id'],
        ]);

        if ($conversation->wasRecentlyCreated) {
            $conversation->messages()->create([
                'sender' => 'vendor',
                'text' => 'Hello! Welcome to our shop. How can I help you today?',
                'read' => true,
            ]);
        }

        $conversation->load('shop');

        return response()->json($conversation);
    }
}
