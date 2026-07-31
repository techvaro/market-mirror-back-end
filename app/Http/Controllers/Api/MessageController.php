<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->messageService->list(
                $request->user()->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'conversation_id' => 'required|exists:chat_conversations,id',
            'text' => 'required|string',
            'image_url' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->messageService->create($validated),
            'message' => 'Message sent',
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->messageService->find($id),
        ]);
    }
}
