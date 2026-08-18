<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePayoutRequest;
use App\Http\Requests\Api\UpdatePayoutBankDetailsRequest;
use App\Http\Resources\PayoutResource;
use App\Services\PayoutService;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', \App\Models\Payout::class);

        $payouts = $this->payoutService->list(
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(PayoutResource::collection($payouts), $payouts);
    }

    public function store(StorePayoutRequest $request)
    {
        $this->authorize('create', \App\Models\Payout::class);

        return response()->json([
            'success' => true,
            'data' => new PayoutResource(
                $this->payoutService->create($request->sanitized(), $request->user())
            ),
            'message' => 'Payout request submitted',
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $payout = $this->payoutService->findForUser((int) $id, $request->user()->id);
        $this->authorize('view', $payout);

        return response()->json([
            'success' => true,
            'data' => new PayoutResource($payout),
        ]);
    }

    public function bankDetails(Request $request)
    {
        $this->authorize('manageBankDetails', \App\Models\Payout::class);

        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'bank_name' => $user->bank_name,
                'bank_account_name' => $user->bank_account_name,
                'bank_account_number' => $user->bank_account_number,
            ],
        ]);
    }

    public function updateBankDetails(UpdatePayoutBankDetailsRequest $request)
    {
        $this->authorize('manageBankDetails', \App\Models\Payout::class);

        $user = $this->payoutService->updateBankDetails($request->user()->id, $request->sanitized());

        return response()->json([
            'success' => true,
            'data' => [
                'bank_name' => $user->bank_name,
                'bank_account_name' => $user->bank_account_name,
                'bank_account_number' => $user->bank_account_number,
            ],
            'message' => 'Bank details saved',
        ]);
    }
}
