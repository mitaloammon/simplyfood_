<?php

namespace Tests\Feature\CashRegister;

use App\Domains\Auth\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashRegisterApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_opens_transacts_and_closes_cash_register(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);

        $openResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/cash/open', [
                'opening_balance' => 100,
            ]);

        $openResponse->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.current_balance', 100);

        $transactionResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/cash/transaction', [
                'type' => 'RECEBIMENTO',
                'amount' => 30,
                'description' => 'Recebimento em dinheiro',
            ]);

        $transactionResponse->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.amount', 30);

        $closeResponse = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->postJson('/api/cash/close', [
                'declared_amount' => 130,
                'blind_closing' => true,
            ]);

        $closeResponse->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.declared_amount', 130)
            ->assertJsonPath('data.expected_amount', 130);
    }
}
