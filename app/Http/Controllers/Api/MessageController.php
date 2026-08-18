<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            $request->get('per_page', 15)
        );

        return $this->paginated(ConversationResource::collection($conversations), $conversations);
    }

    public function store(StoreMessageRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new MessageResource(
                $this->messageService->create($request->sanitized())
            ),
            'message' => 'Message sent',
        ], 201);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => new ConversationResource(
                $this->messageService->find($id)
            ),
        ]);
    }
}
