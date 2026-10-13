<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlutterwaveWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET_HASH = 'test-secret-hash';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.flutterwave.secret_hash' => self::SECRET_HASH,
            'services.flutterwave.secret_key' => 'test-secret-key',
            'services.flutterwave.base_url' => 'https://api.flutterwave.com/v3',
        ]);
    }

    public function test_webhook_without_a_signature_is_rejected(): void
    {
        $this->postJson('/api/payments/webhook', [
            'event' => 'charge.completed',
            'data' => ['id' => 1],
        ])->assertStatus(401);
    }

    public function test_webhook_with_an_invalid_signature_is_rejected(): void
    {
        $this->withHeaders(['verif-hash' => 'not-the-secret'])
            ->postJson('/api/payments/webhook', [
                'event' => 'charge.completed',
                'data' => ['id' => 1],
            ])
            ->assertStatus(401);
    }

    public function test_webhook_fails_closed_when_the_secret_hash_is_not_configured(): void
    {
        config(['services.flutterwave.secret_hash' => null]);

        $this->withHeaders(['verif-hash' => 'anything'])
            ->postJson('/api/payments/webhook', [
                'event' => 'charge.completed',
                'data' => ['id' => 1],
            ])
            ->assertStatus(503);
    }

    public function test_webhook_validates_the_payload_shape(): void
    {
        $this->withHeaders(['verif-hash' => self::SECRET_HASH])
            ->postJson('/api/payments/webhook', ['data' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors('event');
    }

    public function test_charge_completed_with_an_unknown_reference_is_ignored(): void
    {
        [$user, $order, $payment] = $this->makePayableOrder();

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/555/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 555,
                    'status' => 'successful',
                    'currency' => 'NGN',
                    'amount' => 1500,
                    'tx_ref' => 'some-other-ref',
                ],
            ]),
        ]);

        $this->withHeaders(['verif-hash' => self::SECRET_HASH])
            ->postJson('/api/payments/webhook', [
                'event' => 'charge.completed',
                'data' => ['id' => 555, 'tx_ref' => 'some-other-ref', 'status' => 'successful'],
            ])
            ->assertOk();

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('payment_pending', $order->fresh()->status);
    }

    public function test_charge_completed_reconciles_a_matching_payment_and_order(): void
    {
        [$user, $order, $payment] = $this->makePayableOrder();

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/998877/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 998877,
                    'status' => 'successful',
                    'currency' => 'NGN',
                    'amount' => 1500,
                    'tx_ref' => $payment->reference,
                ],
            ]),
        ]);

        $this->withHeaders(['verif-hash' => self::SECRET_HASH])
            ->postJson('/api/payments/webhook', [
                'event' => 'charge.completed',
                'data' => ['id' => 998877, 'tx_ref' => $payment->reference, 'status' => 'successful'],
            ])
            ->assertOk()
            ->assertJson(['received' => true]);

        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    public function test_charge_completed_with_a_mismatched_amount_does_not_confirm_the_order(): void
    {
        [$user, $order, $payment] = $this->makePayableOrder();

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/111222/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 111222,
                    'status' => 'successful',
                    'currency' => 'NGN',
                    'amount' => 10,
                    'tx_ref' => $payment->reference,
                ],
            ]),
        ]);

        $this->withHeaders(['verif-hash' => self::SECRET_HASH])
            ->postJson('/api/payments/webhook', [
                'event' => 'charge.completed',
                'data' => ['id' => 111222, 'tx_ref' => $payment->reference, 'status' => 'successful'],
            ])
            ->assertStatus(502);

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertSame('payment_pending', $order->fresh()->status);
    }

    public function test_transfer_completed_events_update_the_payout(): void
    {
        $user = User::factory()->create();
        $payout = Payout::create([
            'user_id' => $user->id,
            'amount' => 5000,
            'method' => 'bank_transfer',
            'status' => 'processing',
            'transfer_reference' => 'WD-TEST-REF',
        ]);

        $this->withHeaders(['verif-hash' => self::SECRET_HASH])
            ->postJson('/api/payments/webhook', [
                'event' => 'transfer.completed',
                'data' => ['reference' => 'WD-TEST-REF', 'status' => 'SUCCESSFUL'],
            ])
            ->assertOk();

        $this->assertSame('completed', $payout->fresh()->status);
    }

    /**
     * @return array{0: User, 1: Order, 2: Payment}
     */
    private function makePayableOrder(): array
    {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-'.uniqid(),
            'status' => 'payment_pending',
            'delivery_method' => 'delivery',
            'payment_method' => 'flutterwave',
            'subtotal' => 1500,
            'delivery_fee' => 0,
            'total' => 1500,
        ]);

        $payment = Payment::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'amount' => 1500,
            'currency' => 'NGN',
            'method' => 'flutterwave',
            'status' => 'pending',
            'reference' => 'MM-TEST-'.strtoupper(uniqid()),
        ]);

        return [$user, $order, $payment];
    }
}
