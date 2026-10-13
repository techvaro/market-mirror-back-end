<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePayoutRequest;
use App\Http\Requests\Api\UpdatePayoutBankDetailsRequest;
use App\Http\Requests\Api\VerifyBankAccountRequest;
use App\Http\Resources\PayoutResource;
use App\Models\Payout;
use App\Services\FlutterwaveService;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use RuntimeException;

class PayoutController extends Controller
{
    public function __construct(
        private readonly PayoutService $payoutService,
        private readonly FlutterwaveService $flutterwave
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Payout::class);

        $payouts = $this->payoutService->list(
            $request->user()->id,
            $request->get('per_page', 15)
        );

        return $this->paginated(PayoutResource::collection($payouts), $payouts);
    }

    public function summary(Request $request)
    {
        $this->authorize('viewAny', Payout::class);

        return response()->json([
            'success' => true,
            'data' => $this->payoutService->summary($request->user()),
        ]);
    }

    public function banks(Request $request)
    {
        $this->authorize('manageBankDetails', Payout::class);

        try {
            return response()->json(['success' => true, 'data' => $this->flutterwave->nigerianBanks()]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }
    }

    public function store(StorePayoutRequest $request)
    {
        $this->authorize('create', Payout::class);

        try {
            return response()->json([
                'success' => true,
                'data' => new PayoutResource(
                    $this->payoutService->create($request->sanitized(), $request->user())
                ),
                'message' => 'Payout request submitted',
            ], 201);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
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

    public function approve(Request $request, $id)
    {
        $this->authorize('approve', Payout::class);

        try {
            return response()->json([
                'success' => true,
                'data' => new PayoutResource($this->payoutService->approve((int) $id)),
                'message' => 'Payout approved and submitted for transfer',
            ]);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
    }

    public function bankDetails(Request $request)
    {
        $this->authorize('manageBankDetails', Payout::class);

        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'bank_name' => $user->bank_name,
                'bank_code' => $user->bank_code,
                'bank_account_name' => $user->bank_account_name,
                'bank_account_number' => $user->bank_account_number,
            ],
        ]);
    }

    public function updateBankDetails(UpdatePayoutBankDetailsRequest $request)
    {
        $this->authorize('manageBankDetails', Payout::class);

        try {
            $user = $this->payoutService->updateBankDetails($request->user()->id, $request->sanitized());
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 503);
        }

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

    public function verifyBankAccount(VerifyBankAccountRequest $request)
    {
        $this->authorize('manageBankDetails', Payout::class);

        try {
            $supportedBank = collect($this->flutterwave->nigerianBanks())->first(
                fn (array $bank): bool => $bank['code'] === $request->validated('bank_code')
            );
            if (! $supportedBank) {
                return response()->json(['message' => 'Select a supported Nigerian bank.'], 422);
            }

            $accountName = app(FlutterwaveService::class)->resolveBankAccount(
                $request->validated('bank_code'),
                $request->validated('account_number')
            );
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'account_name' => $accountName,
                'account_number' => $request->validated('account_number'),
                'bank_name' => $supportedBank['name'],
                'bank_code' => $request->validated('bank_code'),
                'verified' => true,
            ],
            'message' => 'Bank account verified successfully',
        ]);
    }
}
