<?php

namespace Tests\Feature\Customer;

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
}
