<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PayoutService
{
    public function __construct(private readonly FlutterwaveService $flutterwave) {}

    public function list(int $userId, int $perPage = 15)
    {
        return Payout::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function summary(User $user): array
    {
        $shops = $user->shops()->pluck('name');
        $vendorItems = OrderItem::whereIn('shop_name', $shops);
        $earned = (clone $vendorItems)
            ->whereHas('order', fn ($query) => $query->where('status', 'delivered'))
            ->sum(DB::raw('price * quantity'));
        $pending = (clone $vendorItems)
            ->whereHas('order', fn ($query) => $query->whereNotIn('status', ['delivered', 'cancelled']))
            ->sum(DB::raw('price * quantity'));
        $reserved = Payout::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->sum('amount');

        return [
            'earned' => (float) $earned,
            'pending' => (float) $pending,
            'reserved' => (float) $reserved,
            'available' => max(0, (float) $earned - (float) $reserved),
        ];
    }

    public function find(int $id): Payout
    {
        return Payout::findOrFail($id);
    }

    public function findForUser(int $id, int $userId): Payout
    {
        return Payout::where('user_id', $userId)->findOrFail($id);
    }

    public function create(array $data, User $user): Payout
    {
        if (($data['method'] ?? 'bank_transfer') !== 'bank_transfer') {
            throw new RuntimeException('Only verified bank transfers are currently supported.');
        }

        $shops = $user->shops()->pluck('name');
        $earned = OrderItem::whereIn('shop_name', $shops)
            ->whereHas('order', fn ($query) => $query->where('status', 'delivered'))
            ->sum(DB::raw('price * quantity'));
        $reserved = Payout::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'completed'])
            ->sum('amount');
        $amount = (float) ($data['amount'] ?? 0);
        // Withdrawals always use the account most recently verified and saved on the user.
        $accountNumber = $user->bank_account_number;
        $accountName = $user->bank_account_name;
        $bankName = $user->bank_name;
        $bankCode = $user->bank_code;

        if ($amount <= 0 || $amount > ((float) $earned - (float) $reserved)) {
            throw new RuntimeException('Withdrawal exceeds your available vendor balance.');
        }

        if (! $accountNumber || ! $accountName || ! $bankName || ! $bankCode) {
            throw new RuntimeException('Save and verify your bank account before requesting a withdrawal.');
        }

        $payout = Payout::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'method' => 'bank_transfer',
            'status' => 'pending',
            'account_name' => $accountName,
            'account_number' => $accountNumber,
            'bank_name' => $bankName,
            'bank_code' => $bankCode,
            'note' => 'Awaiting admin approval',
            'transfer_reference' => 'WD-'.strtoupper(Str::random(20)),
        ]);

        return $payout;
    }

    public function approve(int $id): Payout
    {
        $payout = Payout::findOrFail($id);
        if ($payout->status !== 'pending') {
            throw new RuntimeException('Only pending payout requests can be approved.');
        }

        try {
            $payout->update(['status' => 'processing', 'note' => 'Approved by admin; transfer processing']);
            $providerResponse = app(FlutterwaveService::class)->createTransfer($payout);
            $payout->update(['status' => 'processing', 'provider_response' => $providerResponse]);
        } catch (RuntimeException $exception) {
            $payout->update(['status' => 'failed', 'note' => $exception->getMessage()]);
            throw $exception;
        }

        return $payout->fresh();
    }

    public function updateBankDetails(int $userId, array $data): User
    {
        $user = User::findOrFail($userId);
        $bank = collect($this->flutterwave->nigerianBanks())
            ->firstWhere('code', (string) $data['bank_code']);
        if (! $bank) {
            throw ValidationException::withMessages(['bank_code' => ['Select a supported Nigerian bank.']]);
        }

        $accountName = $this->flutterwave->resolveBankAccount(
            (string) $data['bank_code'],
            (string) $data['bank_account_number']
        );
        $data['bank_name'] = $bank['name'];
        $data['bank_account_name'] = $accountName;
        $user->update($data);

        return $user;
    }
}
