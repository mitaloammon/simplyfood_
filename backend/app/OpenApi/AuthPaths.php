<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

final class AuthPaths
{
    #[OA\Post(
        path: '/api/auth/login',
        operationId: 'authLogin',
        summary: 'Autenticar usuário',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest')
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login realizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 401, description: 'Credenciais inválidas', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function login(): void
    {
    }

    #[OA\Post(
        path: '/api/auth/logout',
        operationId: 'authLogout',
        summary: 'Encerrar a sessão atual',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Sessão encerrada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 401, description: 'Não autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function logout(): void
    {
    }

    #[OA\Get(
        path: '/api/auth/me',
        operationId: 'authMe',
        summary: 'Consultar o usuário autenticado',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Usuário autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 401, description: 'Não autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function me(): void
    {
    }
}
