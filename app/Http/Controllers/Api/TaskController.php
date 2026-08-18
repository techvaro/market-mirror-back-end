<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTaskRequest;
use App\Http\Requests\Api\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        $tasks = $this->taskService->list(
            $request->only(['status', 'priority', 'mapper_id']),
            $request->user()?->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(TaskResource::collection($tasks), $tasks);
    }

    public function show($id)
    {
        $task = $this->taskService->find($id);

        $this->authorize('view', $task);

        return response()->json([
            'success' => true,
            'data' => new TaskResource($task),
        ]);
    }

    public function store(StoreTaskRequest $request)
    {
        $this->authorize('manage', Task::class);

        return response()->json([
            'success' => true,
            'data' => new TaskResource($this->taskService->create($request->sanitized())),
            'message' => 'Task created',
        ], 201);
    }

    public function update(UpdateTaskRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new TaskResource($this->taskService->update($id, $request->sanitized())),
            'message' => 'Task updated',
        ]);
    }
}
