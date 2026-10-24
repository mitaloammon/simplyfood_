<?php

use App\Models\User;
use Illuminate\Support\Str;

it('retorna prontidão falsa e bloqueia operações quando o usuário não tem estabelecimento', function () {
    $user = new User([
        'id' => (string) Str::uuid(),
        'establishment_id' => null,
        'name' => 'Administrator',
        'email' => 'admin-without-establishment@simplycore.test',
        'password' => 'password',
        'role' => 'ADMIN',
        'status' => 'ACTIVE',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('data.installation_ready', false);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/categories')
        ->assertConflict()
        ->assertExactJson([
            'status' => 'error',
            'code' => 'ESTABLISHMENT_SETUP_REQUIRED',
            'data' => null,
            'message' => 'A configuração do estabelecimento está pendente',
        ]);
});
