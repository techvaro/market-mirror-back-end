<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreConversationRequest;
use App\Http\Requests\Api\StoreMessageRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Services\MessageService;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private readonly MessageService $messageService
    ) {}

    public function index(Request $request)
    {
        $conversations = $this->messageService->list(
            $request->user()->id,
            $request->get('per_page', 15),
            $request->user()->role
        );

        return $this->paginated(ConversationResource::collection($conversations), $conversations);
    }

    public function store(StoreMessageRequest $request)
    {
        $message = match ($request->user()->role) {
            'vendor' => $this->messageService->createAsVendor($request->sanitized(), $request->user()->id),
            'admin' => $this->messageService->createAsAdmin($request->sanitized(), $request->user()->id),
            default => $this->messageService->create($request->sanitized(), $request->user()->id),
        };

        return response()->json([
            'success' => true,
            'data' => new MessageResource($message),
            'message' => 'Message sent',
        ], 201);
    }

    public function storeConversation(StoreConversationRequest $request)
    {
        $conversation = $this->messageService->findOrCreateConversation(
            $request->user()->id,
            $request->sanitized()['shop_id']
        );

        return response()->json([
            'success' => true,
            'data' => new ConversationResource(
                $conversation->load(['shop', 'messages'])
            ),
            'message' => 'Conversation started',
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $conversation = $this->messageService->find(
            $id,
            $request->user()->id,
            $request->user()->role
        );

        return response()->json([
            'success' => true,
            'data' => new ConversationResource($conversation),
        ]);
    }
}
