<?php

namespace App\Http\Controllers\Api;

use App\Actions\Disputes\ResolveDisputeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ResolveDisputeRequest;
use App\Http\Requests\Api\StoreDisputeRequest;
use App\Http\Requests\Api\UpdateDisputeRequest;
use App\Http\Resources\DisputeResource;
use App\Models\Dispute;
use App\Services\DisputeService;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    public function __construct(
        private readonly DisputeService $disputeService,
        private readonly ResolveDisputeAction $resolveDisputeAction
    ) {}

    public function index(Request $request)
    {
        $disputes = $this->disputeService->list(
            $request->only(['status']),
            $request->user()->role,
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(DisputeResource::collection($disputes), $disputes);
    }

    public function store(StoreDisputeRequest $request)
    {
        $dispute = $this->disputeService->create($request->sanitized(), $request->user()->id);

        return response()->json([
            'success' => true,
            'data' => new DisputeResource($dispute->load('order', 'user')),
            'message' => 'Dispute filed',
        ], 201);
    }

    public function update(UpdateDisputeRequest $request, $id)
    {
        $dispute = $this->disputeService->find($id);

        $this->authorize('update', $dispute);

        return response()->json([
            'success' => true,
            'data' => new DisputeResource($this->disputeService->update($id, $request->sanitized())->load('order', 'user')),
            'message' => 'Dispute updated',
        ]);
    }

    public function resolve(ResolveDisputeRequest $request, $id)
    {
        $dispute = $this->disputeService->find($id);

        $this->authorize('resolve', Dispute::class);

        $resolved = $this->resolveDisputeAction->execute(
            $dispute,
            $request->validated('action'),
            $request->user(),
            $request->validated('note')
        );

        return response()->json([
            'success' => true,
            'data' => new DisputeResource($resolved),
            'message' => 'Dispute resolved',
        ]);
    }
}
