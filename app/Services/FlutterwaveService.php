<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\Refund;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class FlutterwaveService
{
    private function request(bool $json = true)
    {
        $secret = config('services.flutterwave.secret_key');

        if (! $secret) {
            throw new RuntimeException('Flutterwave is not configured.');
        }

        $request = Http::baseUrl(config('services.flutterwave.base_url'))
            ->withToken($secret)
            ->acceptJson()
            ->timeout(20)
            ->retry(2, 250);

        return $json ? $request->asJson() : $request;
    }

    public function initialize(Order $order, array $customer): array
    {
        $reference = 'MM-'.strtoupper(Str::random(20));
        $payment = Payment::create([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'amount' => $order->total,
            'currency' => config('services.flutterwave.currency', 'NGN'),
            'method' => 'flutterwave',
            'status' => 'pending',
            'reference' => $reference,
            'metadata' => ['order_number' => $order->order_number],
        ]);

        $response = $this->request()->post('/payments', [
            'tx_ref' => $reference,
            'amount' => (string) $order->total,
            'currency' => $payment->currency,
            'redirect_url' => config('services.flutterwave.redirect_url'),
            'payment_options' => 'card,banktransfer,ussd',
            'customer' => [
                'email' => $customer['email'] ?? $order->user->email,
                'phonenumber' => $customer['phone'] ?? $order->user->phone,
                'name' => $customer['name'] ?? $order->user->name,
            ],
            'customizations' => ['title' => config('app.name', 'Market Mirror')],
            'meta' => ['order_id' => $order->id, 'payment_id' => $payment->id],
        ]);

        if ($response->failed() || $response->json('status') !== 'success') {
            $payment->update(['status' => 'failed', 'metadata' => ['gateway_error' => $response->json()]]);
            throw new RuntimeException('Flutterwave could not initialize this payment.');
        }

        return [
            'payment_id' => $payment->id,
            'tx_ref' => $reference,
            'link' => $response->json('data.link'),
        ];
    }

    public function verify(string $transactionId): array
    {
        $response = $this->request()->get('/transactions/'.urlencode($transactionId).'/verify');

        if ($response->failed() || $response->json('status') !== 'success') {
            throw new RuntimeException('Flutterwave transaction verification failed.');
        }

        return $response->json('data', []);
    }

    public function createTransfer(Payout $payout): array
    {
        $response = $this->request()->post('/transfers', [
            'account_bank' => $payout->bank_code ?: config('services.flutterwave.transfer_bank_code'),
            'account_number' => $payout->account_number,
            'amount' => (string) $payout->amount,
            'currency' => config('services.flutterwave.currency', 'NGN'),
            'reference' => $payout->transfer_reference,
            'beneficiary_name' => $payout->account_name,
            'narration' => 'Market Mirror vendor withdrawal',
        ]);

        if ($response->failed() || $response->json('status') !== 'success') {
            throw new RuntimeException('Flutterwave could not create the vendor transfer.');
        }

        return $response->json('data', []);
    }

    /** @return array<int, array{name: string, code: string}> */
    public function nigerianBanks(): array
    {
        return Cache::remember('flutterwave:nigerian-banks', now()->addHours(12), function (): array {
            try {
                $response = $this->request(false)->get('/banks/NG');
            } catch (Throwable) {
                throw new RuntimeException('Flutterwave bank list is unavailable. Check that FLW_SECRET_KEY is active and permitted to access Nigerian banks.');
            }

            if ($response->failed()) {
                throw new RuntimeException('Flutterwave bank list is unavailable. Check that FLW_SECRET_KEY is active and permitted to access Nigerian banks.');
            }

            if ($response->failed() || $response->json('status') !== 'success') {
                throw new RuntimeException('Unable to load Nigerian banks right now.');
            }

            $received = collect($response->json('data', []));
            $banks = $received
                ->filter(function (array $bank): bool {
                    $type = strtolower((string) ($bank['type'] ?? ''));

                    return ! empty($bank['code'])
                        && ! empty($bank['name'])
                        && ! str_contains($type, 'mobile');
                })
                ->map(fn (array $bank): array => [
                    'name' => (string) $bank['name'],
                    'code' => (string) $bank['code'],
                ])
                ->unique('code')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();

            Log::info('Flutterwave Nigerian bank list normalized', [
                'received' => $received->count(),
                'filtered' => collect($banks)->count(),
                'returned' => count($banks),
            ]);

            return $banks;
        });
    }

    public function resolveBankAccount(string $bankCode, string $accountNumber): string
    {
        try {
            $response = $this->request()->post('/accounts/resolve', [
                'account_bank' => $bankCode,
                'account_number' => $accountNumber,
            ]);
        } catch (Throwable) {
            throw new RuntimeException('Flutterwave could not verify this bank account.');
        }

        if ($response->failed() || $response->json('status') !== 'success') {
            throw new RuntimeException('Flutterwave could not verify this bank account.');
        }

        $accountName = (string) $response->json('data.account_name', '');
        if ($accountName === '') {
            throw new RuntimeException('No account name was returned for these bank details.');
        }

        return $accountName;
    }

    public function createRefundTransfer(Refund $refund): array
    {
        $response = $this->request()->post('/transfers', [
            'account_bank' => $refund->bank_code,
            'account_number' => $refund->account_number,
            'amount' => (string) $refund->amount,
            'currency' => $refund->currency,
            'reference' => $refund->transfer_reference,
            'beneficiary_name' => $refund->account_name,
            'narration' => 'Market Mirror order refund',
        ]);

        if ($response->failed() || $response->json('status') !== 'success') {
            throw new RuntimeException('Flutterwave could not send the refund.');
        }

        return $response->json('data', []);
    }
}
