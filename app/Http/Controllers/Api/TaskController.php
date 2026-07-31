<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->taskService->list(
                $request->only(['status', 'priority', 'mapper_id']),
                $request->user()?->id,
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->taskService->find($id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mapper_id' => 'required|exists:mappers,id',
            'title' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|in:vendor_registration,shop_mapping,re_mapping,verification_visit',
            'market' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'required|date',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->taskService->create($validated),
            'message' => 'Task created',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,in_progress,completed,rejected,cancelled',
            'corrections' => 'sometimes|nullable|array',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->taskService->update($id, $validated),
            'message' => 'Task updated',
        ]);
    }
}
