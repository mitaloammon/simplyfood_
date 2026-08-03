<?php

namespace Tests\Feature\Dashboard;

use App\Domains\Auth\User\User;
use App\Domains\Customer\Customer;
use App\Domains\Order\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardMetricsApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_consolidated_dashboard_metrics_from_database(): void
    {
        $user = User::factory()->create([
            'role' => 'ADMIN',
        ]);

        $anotherUser = User::factory()->create([
            'role' => 'OPERATOR',
        ]);

        Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Um',
            'phone' => '11999999999',
        ]);

        Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Dois',
            'phone' => '11888888888',
        ]);

        $customer = Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Tres',
            'phone' => '11777777777',
        ]);

        $softDeletedCustomer = Customer::query()->create([
            'user_id' => $user->id,
            'name' => 'Cliente Removido',
            'phone' => '11666666666',
        ]);
        $softDeletedCustomer->delete();

        Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'WAITING_PAYMENT',
            'total' => 50.00,
        ]);

        Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'PREPARING',
            'total' => 80.00,
        ]);

        Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'DELIVERED',
            'total' => 120.00,
        ]);

        Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'CANCELLED',
            'total' => 40.00,
        ]);

        $softDeletedOrder = Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'PAID',
            'total' => 70.00,
        ]);
        $softDeletedOrder->delete();

        Order::query()->create([
            'user_id' => $anotherUser->id,
            'customer_id' => null,
            'status' => 'PAID',
            'total' => 999.00,
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->getJson('/api/dashboard/metrics');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Metricas do dashboard carregadas com sucesso.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                    ],
                ],
            ]);

        $metrics = collect($response->json('data.metrics'))->keyBy('key');

        $this->assertSame('3', $metrics->get('customers')['value']);
        $this->assertSame('2', $metrics->get('orders_active')['value']);
        $this->assertSame('R$ 250,00', $metrics->get('revenue_total')['value']);
        $this->assertSame('R$ 83,33', $metrics->get('average_ticket')['value']);
    }
}
