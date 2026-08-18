<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AssignTaskRequest;
use App\Http\Requests\Api\CreateMapperRequest;
use App\Http\Requests\Api\UpdateMapperRequest;
use App\Http\Resources\MapperResource;
use App\Http\Resources\TaskResource;
use App\Services\MapperService;
use Illuminate\Http\Request;

class MapperController extends Controller
{
    public function __construct(
        private readonly MapperService $mapperService
    ) {}

    public function index(Request $request)
    {
        $mappers = $this->mapperService->list(
            $request->only(['status', 'region', 'search']),
            $request->get('per_page', 15)
        );

        return $this->paginated(MapperResource::collection($mappers), $mappers);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => new MapperResource($this->mapperService->find($id)),
        ]);
    }

    public function store(CreateMapperRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new MapperResource($this->mapperService->create($request->sanitized())),
            'message' => 'Mapper created',
        ], 201);
    }

    public function update(UpdateMapperRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new MapperResource($this->mapperService->update($id, $request->sanitized())),
            'message' => 'Mapper updated',
        ]);
    }

    public function destroy($id)
    {
        $this->mapperService->suspend($id);

        return response()->json([
            'success' => true,
            'message' => 'Mapper suspended',
        ]);
    }

    public function dashboard()
    {
        return response()->json([
            'success' => true,
            'data' => $this->mapperService->dashboard(),
        ]);
    }

    public function assignTask(AssignTaskRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new TaskResource($this->mapperService->assignTask($request->sanitized())),
            'message' => 'Task assigned',
        ], 201);
    }
}
