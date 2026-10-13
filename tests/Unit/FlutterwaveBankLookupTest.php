<?php

namespace Tests\Unit;

use App\Services\FlutterwaveService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class FlutterwaveBankLookupTest extends TestCase
{
    public function test_nigerian_banks_are_normalized_and_mobile_money_is_excluded(): void
    {
        config(['services.flutterwave.secret_key' => 'test-secret']);
        Cache::forget('flutterwave:nigerian-banks');
        Http::fake([
            'https://api.flutterwave.com/v3/banks/NG' => Http::response([
                'status' => 'success',
                'data' => [
                    ['name' => 'Access Bank', 'code' => '044', 'type' => 'BANK'],
                    ['name' => 'GTBank', 'code' => '058', 'type' => 'BANK'],
                    ['name' => 'Zerobank', 'code' => '004', 'type' => 'BANK'],
                    ['name' => 'Mobile Wallet', 'code' => '900', 'type' => 'MOBILE MONEY'],
                ],
            ]),
        ]);

        $banks = app(FlutterwaveService::class)->nigerianBanks();

        $this->assertCount(3, $banks);
        $this->assertSame(['Access Bank', 'GTBank', 'Zerobank'], array_column($banks, 'name'));
    }

    public function test_account_resolution_uses_the_documented_post_endpoint(): void
    {
        config(['services.flutterwave.secret_key' => 'test-secret']);
        Http::fake([
            'https://api.flutterwave.com/v3/accounts/resolve' => Http::response([
                'status' => 'success',
                'data' => ['account_name' => 'Ada Example', 'account_number' => '0123456789'],
            ]),
        ]);

        $accountName = app(FlutterwaveService::class)->resolveBankAccount('004', '0123456789');

        $this->assertSame('Ada Example', $accountName);
        Http::assertSent(fn ($request) => $request->url() === 'https://api.flutterwave.com/v3/accounts/resolve'
            && $request['account_bank'] === '004'
            && $request['account_number'] === '0123456789');
    }

    public function test_account_resolution_rejects_provider_failures(): void
    {
        config(['services.flutterwave.secret_key' => 'test-secret']);
        Http::fake(['https://api.flutterwave.com/v3/accounts/resolve' => Http::response(['status' => 'error'], 400)]);

        $this->expectException(RuntimeException::class);
        app(FlutterwaveService::class)->resolveBankAccount('004', '0123456789');
    }
}
