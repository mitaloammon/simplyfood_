<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

final class TablePaths
{
    #[OA\Get(
        path: '/api/tables', operationId: 'tablesIndex', summary: 'Listar mesas',
        security: [['sanctum' => []]], tags: ['Tables'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [new OA\Response(response: 200, description: 'Mesas listadas', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function tablesIndex(): void {}

    #[OA\Post(
        path: '/api/tables', operationId: 'tablesStore', summary: 'Criar mesa',
        security: [['sanctum' => []]], tags: ['Tables'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TablePayload')),
        responses: [
            new OA\Response(response: 201, description: 'Mesa criada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function tablesStore(): void {}

    #[OA\Patch(
        path: '/api/tables/{table}', operationId: 'tablesUpdate', summary: 'Atualizar mesa',
        security: [['sanctum' => []]], tags: ['Tables'],
        parameters: [new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/TablePayload')),
        responses: [
            new OA\Response(response: 200, description: 'Mesa atualizada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Mesa não encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function tablesUpdate(): void {}

    #[OA\Patch(
        path: '/api/tables/{table}/status', operationId: 'tablesUpdateStatus', summary: 'Atualizar status da mesa',
        security: [['sanctum' => []]], tags: ['Tables'],
        parameters: [new OA\Parameter(name: 'table', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['status'], properties: [new OA\Property(property: 'status', type: 'string', enum: ['FREE', 'OCCUPIED', 'RESERVED', 'BILLING'])]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Status atualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Conflito de estado da mesa', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function tablesUpdateStatus(): void {}

    #[OA\Get(
        path: '/api/commands', operationId: 'commandsIndex', summary: 'Listar comandas',
        security: [['sanctum' => []]], tags: ['Tables'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [new OA\Response(response: 200, description: 'Comandas listadas', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function commandsIndex(): void {}

    #[OA\Post(
        path: '/api/commands', operationId: 'commandsStore', summary: 'Abrir comanda',
        security: [['sanctum' => []]], tags: ['Tables'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['code', 'table_id'], properties: [
                new OA\Property(property: 'code', type: 'string', maxLength: 50),
                new OA\Property(property: 'table_id', type: 'string', format: 'uuid'),
            ]
        )),
        responses: [
            new OA\Response(response: 201, description: 'Comanda aberta', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Conflito ao abrir comanda', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function commandsStore(): void {}

    #[OA\Patch(
        path: '/api/commands/{command}/status', operationId: 'commandsUpdateStatus', summary: 'Atualizar status da comanda',
        security: [['sanctum' => []]], tags: ['Tables'],
        parameters: [new OA\Parameter(name: 'command', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['status'], properties: [new OA\Property(property: 'status', type: 'string', enum: ['OPEN', 'CLOSED', 'BLOCKED'])]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Status atualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Comanda não encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Conflito de estado da comanda', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function commandsUpdateStatus(): void {}
}
