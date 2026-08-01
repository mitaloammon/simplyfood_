<?php

namespace Tests\Feature\Dashboard;

use App\Domains\Auth\User\User;
use App\Domains\Customer\Customer;
use App\Domains\Delivery\Delivery;
use App\Domains\Order\Order;
use Carbon\Carbon;
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

        Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'PAID',
            'total' => 120.00,
            'created_at' => Carbon::today()->addHours(10),
        ]);

        $orderWithDelivery = Order::query()->create([
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'status' => 'DELIVERED',
            'total' => 80.50,
            'created_at' => Carbon::today()->addHours(12),
        ]);

        $delivery = Delivery::query()->create([
            'order_id' => $orderWithDelivery->id,
            'status' => 'DELIVERED',
            'delivered_at' => Carbon::now()->addMinutes(30),
        ]);

        $delivery->created_at = Carbon::now();
        $delivery->save();

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
        $this->assertSame('2', $metrics->get('orders_today')['value']);
        $this->assertSame('R$ 200,50', $metrics->get('revenue_today')['value']);

        $deliveryAverage = $metrics->get('delivery_avg')['value'];
        $this->assertStringEndsWith(' min', $deliveryAverage);
        $this->assertGreaterThanOrEqual(0, (int) str_replace(' min', '', $deliveryAverage));
    }
}
