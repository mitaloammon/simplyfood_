<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

final class CashPaths
{
    #[OA\Post(
        path: '/api/cash/open', operationId: 'cashOpen', summary: 'Abrir caixa',
        security: [['sanctum' => []]], tags: ['Cash'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CashOpenPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Caixa aberto', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Caixa já possui turno aberto', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function open(): void {}

    #[OA\Get(
        path: '/api/cash/current', operationId: 'cashCurrent', summary: 'Consultar caixa atual',
        security: [['sanctum' => []]], tags: ['Cash'],
        responses: [new OA\Response(response: 200, description: 'Estado do caixa atual', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function current(): void {}

    #[OA\Post(
        path: '/api/cash/movements', operationId: 'cashMovement', summary: 'Registrar movimento de caixa',
        security: [['sanctum' => []]], tags: ['Cash'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CashMovementPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Movimento registrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Não há turno aberto', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function movement(): void {}

    #[OA\Get(
        path: '/api/cash/history', operationId: 'cashHistory', summary: 'Listar histórico de caixa',
        security: [['sanctum' => []]], tags: ['Cash'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [new OA\Response(response: 200, description: 'Histórico listado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function history(): void {}

    #[OA\Post(
        path: '/api/cash/close', operationId: 'cashClose', summary: 'Fechar caixa',
        security: [['sanctum' => []]], tags: ['Cash'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CashClosePayload')),
        responses: [
            new OA\Response(response: 200, description: 'Caixa fechado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Conflito ao fechar caixa', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function close(): void {}
}
