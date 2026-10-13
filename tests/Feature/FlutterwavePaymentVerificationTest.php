<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FlutterwavePaymentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.flutterwave.secret_key' => 'test-secret-key',
            'services.flutterwave.base_url' => 'https://api.flutterwave.com/v3',
        ]);
    }

    public function test_verify_endpoint_requires_authentication(): void
    {
        $this->postJson('/api/payments/verify', ['transaction_id' => '12345'])
            ->assertStatus(401);
    }

    public function test_verify_endpoint_rejects_an_invalid_transaction_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'api')
            ->postJson('/api/payments/verify', ['transaction_id' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors('transaction_id');
    }

    public function test_a_user_cannot_verify_another_users_payment(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        [, $payment] = $this->makePayableOrder($owner);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/777/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 777,
                    'status' => 'successful',
                    'currency' => 'NGN',
                    'amount' => 1500,
                    'tx_ref' => $payment->reference,
                ],
            ]),
        ]);

        $this->actingAs($attacker, 'api')
            ->postJson('/api/payments/verify', ['transaction_id' => '777'])
            ->assertStatus(404);

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_a_user_can_verify_their_own_payment(): void
    {
        $owner = User::factory()->create();
        [$order, $payment] = $this->makePayableOrder($owner);

        Http::fake([
            'https://api.flutterwave.com/v3/transactions/888/verify' => Http::response([
                'status' => 'success',
                'data' => [
                    'id' => 888,
                    'status' => 'successful',
                    'currency' => 'NGN',
                    'amount' => 1500,
                    'tx_ref' => $payment->reference,
                ],
            ]),
        ]);

        $this->actingAs($owner, 'api')
            ->postJson('/api/payments/verify', ['transaction_id' => '888'])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('completed', $payment->fresh()->status);
        $this->assertSame('confirmed', $order->fresh()->status);
    }

    /**
     * @return array{0: Order, 1: Payment}
     */
    private function makePayableOrder(User $user): array
    {
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

        return [$order, $payment];
    }
}
