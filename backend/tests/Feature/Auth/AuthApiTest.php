<?php

namespace Tests\Feature\Auth;

use App\Domains\Auth\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_logs_in_with_valid_credentials_and_returns_token_and_user(): void
    {
        $password = 'secret123';

        $user = User::factory()->create([
            'email' => 'auth-success@test.com',
            'password' => bcrypt($password),
            'role' => 'ADMIN',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.email', $user->email);

        $this->assertStringStartsWith('valid-', $response->json('data.token'));
    }

    /** @test */
    public function it_returns_unauthorized_for_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'auth-fail@test.com',
            'password' => bcrypt('secret123'),
            'role' => 'ADMIN',
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'auth-fail@test.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    /** @test */
    public function it_blocks_protected_routes_without_token(): void
    {
        $response = $this->getJson('/api/orders');

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error');
    }

    /** @test */
    public function it_returns_forbidden_when_role_is_not_allowed_for_protected_routes(): void
    {
        $user = User::factory()->create([
            'role' => 'DELIVERY',
        ]);

        $response = $this
            ->withHeaders(['Authorization' => 'Bearer valid-' . $user->id])
            ->getJson('/api/orders');

        $response->assertStatus(403)
            ->assertJsonPath('status', 'error');
    }
}
