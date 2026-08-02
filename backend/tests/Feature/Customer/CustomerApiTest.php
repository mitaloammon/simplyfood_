<?php

namespace Tests\Feature\Customer;

use App\Domains\Auth\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_customer_via_api()
    {
        $payload = [
            'name' => 'Maria Oliveira',
            'email' => 'maria@test.com',
            'phone' => '11988888888',
            'whatsapp' => '11988888888',
            'cpf_cnpj' => '98765432100'
        ];

        $response = $this->postJson('/api/customers', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'data' => ['id', 'name', 'email', 'whatsapp'],
                     'message'
                 ]);

        $this->assertDatabaseHas('customers', ['email' => 'maria@test.com']);
    }

    /** @test */
    public function it_associates_customer_to_authenticated_user_when_bearer_token_is_sent(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $payload = [
            'name' => 'Carlos Token',
            'phone' => '11977776666',
            'whatsapp' => '11977776666',
            'email' => 'carlos-token@test.com',
            'cpf_cnpj' => '12312312399',
        ];

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/customers', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('customers', [
            'email' => 'carlos-token@test.com',
            'user_id' => $user->id,
        ]);
    }
}
