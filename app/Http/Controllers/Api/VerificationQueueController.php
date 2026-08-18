<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateVerificationQueueRequest;
use App\Http\Resources\VerificationQueueResource;
use App\Services\VerificationQueueService;
use Illuminate\Http\Request;

class VerificationQueueController extends Controller
{
    public function __construct(
        private readonly VerificationQueueService $verificationService
    ) {}

    public function index(Request $request)
    {
        $items = $this->verificationService->list(
            $request->only(['status']),
            $request->get('per_page', 15)
        );

        return $this->paginated(VerificationQueueResource::collection($items), $items);
    }

    public function update(UpdateVerificationQueueRequest $request, $id)
    {
        return response()->json([
            'success' => true,
            'data' => new VerificationQueueResource(
                $this->verificationService->update($id, $request->sanitized())
            ),
            'message' => 'Verification updated',
        ]);
    }
}
