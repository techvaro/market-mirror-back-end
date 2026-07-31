<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MapperService;
use Illuminate\Http\Request;

class MapperController extends Controller
{
    public function __construct(
        private readonly MapperService $mapperService
    ) {}

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->mapperService->list(
                $request->only(['status', 'region', 'search']),
                $request->get('per_page', 15)
            ),
        ]);
    }

    public function show($id)
    {
        return response()->json([
            'success' => true,
            'data' => $this->mapperService->find($id),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'employee_id' => 'required|string|unique:mappers,employee_id',
            'assigned_market' => 'required|string',
            'assigned_region' => 'required|string',
            'supervisor' => 'nullable|string',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->mapperService->create($validated),
            'message' => 'Mapper created',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'assigned_market' => 'sometimes|string',
            'assigned_region' => 'sometimes|string',
            'mapper_status' => 'sometimes|in:active,inactive,suspended',
            'supervisor' => 'sometimes|nullable|string',
            'notes' => 'sometimes|nullable|string',
            'name' => 'sometimes|string|max:255',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->mapperService->update($id, $validated),
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

    public function assignTask(Request $request)
    {
        $validated = $request->validate([
            'mapper_id' => 'required|exists:mappers,id',
            'title' => 'required|string',
            'type' => 'required|in:vendor_registration,shop_mapping,re_mapping,verification_visit',
            'market' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'required|date',
        ]);

        return response()->json([
            'success' => true,
            'data' => $this->mapperService->assignTask($validated),
            'message' => 'Task assigned',
        ], 201);
    }
}
