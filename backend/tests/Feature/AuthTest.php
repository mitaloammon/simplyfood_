<?php

use App\Models\Establishment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function createAuthEstablishment(string $status = 'ACTIVE'): Establishment
{
    return Establishment::query()->create([
        'id' => (string) Str::uuid(),
        'name' => 'Estabelecimento de teste',
        'status' => $status,
    ]);
}

function createAuthUser(array $attributes = []): User
{
    $establishmentId = $attributes['establishment_id'] ?? createAuthEstablishment()->id;

    return User::query()->create(array_merge([
        'id' => (string) Str::uuid(),
        'establishment_id' => $establishmentId,
        'name' => 'Administrador',
        'email' => 'admin@simplyfood.test',
        'password' => Hash::make('password'),
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ], $attributes));
}

it('faz login com credenciais válidas', function () {
    $user = createAuthUser();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.token_type', 'Bearer')
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.establishment_id', $user->establishment_id)
        ->assertJsonPath('message', 'Login realizado com sucesso')
        ->assertJsonStructure(['status', 'data' => ['token', 'token_type', 'user'], 'message']);
});

it('recusa senha errada', function () {
    $user = createAuthUser();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'senha-errada',
    ])->assertUnauthorized()
        ->assertExactJson([
            'status' => 'error',
            'data' => null,
            'message' => 'Credenciais inválidas',
        ]);
});

it('recusa usuário inativo', function () {
    $user = createAuthUser(['status' => 'INACTIVE']);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertUnauthorized()
        ->assertJsonPath('status', 'error');
});

it('recusa me sem token', function () {
    $this->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertExactJson([
            'status' => 'error',
            'data' => null,
            'message' => 'Não autenticado',
        ]);
});

it('retorna me com token', function () {
    $user = createAuthUser();
    $token = $user->createToken('test')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertExactJson([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'establishment_id' => $user->establishment_id,
                'installation_ready' => true,
            ],
            'message' => 'OK',
        ]);

    $this->withToken($token)
        ->getJson('/api/categories')
        ->assertOk();
});

it('permite autenticar, mas bloqueia operações de negócio quando o estabelecimento está inativo', function () {
    $establishment = createAuthEstablishment('INACTIVE');
    $user = createAuthUser(['establishment_id' => $establishment->id]);

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertOk()
        ->assertJsonPath('data.user.installation_ready', false);

    $this->actingAs($user)
        ->getJson('/api/categories')
        ->assertConflict()
        ->assertJsonPath('code', 'ESTABLISHMENT_SETUP_REQUIRED');

    $this->actingAs($user)
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.installation_ready', false);
});

it('bloqueia um token existente quando a conta é desativada, mas permite encerrar a sessão', function () {
    $user = createAuthUser();
    $token = $user->createToken('test')->plainTextToken;

    $user->update(['status' => 'INACTIVE']);

    $this->withToken($token)
        ->getJson('/api/auth/me')
        ->assertUnauthorized()
        ->assertJsonPath('code', 'ACCOUNT_INACTIVE');

    $this->withToken($token)
        ->getJson('/api/categories')
        ->assertUnauthorized()
        ->assertJsonPath('code', 'ACCOUNT_INACTIVE');

    $this->withToken($token)
        ->postJson('/api/auth/logout')
        ->assertOk();
});
