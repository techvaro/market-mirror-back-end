<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreStateRequest;
use App\Http\Resources\StateResource;
use App\Services\StateService;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function __construct(
        private readonly StateService $stateService
    ) {}

    /**
     * Public: active states for the buyer/vendor location pickers.
     */
    public function index()
    {
        $states = $this->stateService->activeList();

        return response()->json([
            'success' => true,
            'data' => StateResource::collection($states),
        ]);
    }

    public function adminIndex(Request $request)
    {
        $states = $this->stateService->all($request->get('per_page', 15));

        return $this->paginated(StateResource::collection($states), $states);
    }

    public function store(StoreStateRequest $request)
    {
        return response()->json([
            'success' => true,
            'data' => new StateResource($this->stateService->create($request->sanitized())),
            'message' => 'State created',
        ], 201);
    }

    public function update(StoreStateRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new StateResource($this->stateService->update($id, $request->sanitized())),
            'message' => 'State updated',
        ]);
    }

    public function toggleActive(Request $request, $id)
    {
        $request->validate(['is_active' => 'required|boolean']);

        return response()->json([
            'success' => true,
            'data' => new StateResource($this->stateService->setActive($id, (bool) $request->is_active)),
            'message' => 'State updated',
        ]);
    }

    public function destroy($id)
    {
        $this->stateService->delete($id);

        return response()->json([
            'success' => true,
            'message' => 'State deleted',
        ]);
    }
}
