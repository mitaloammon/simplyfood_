<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

final class CustomerPaths
{
    #[OA\Get(
        path: '/api/customers', operationId: 'customersIndex', summary: 'Listar clientes',
        security: [['sanctum' => []]], tags: ['Customers'],
        parameters: [
            new OA\Parameter(name: 'q', in: 'query', schema: new OA\Schema(type: 'string', maxLength: 255)),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [new OA\Response(response: 200, description: 'Clientes listados', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/customers', operationId: 'customersStore', summary: 'Criar cliente',
        security: [['sanctum' => []]], tags: ['Customers'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CustomerPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Cliente criado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/customers/{customer}', operationId: 'customersShow', summary: 'Consultar cliente',
        security: [['sanctum' => []]], tags: ['Customers'],
        parameters: [new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Cliente encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Cliente não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function show(): void {}

    #[OA\Patch(
        path: '/api/customers/{customer}', operationId: 'customersUpdate', summary: 'Atualizar cliente',
        security: [['sanctum' => []]], tags: ['Customers'],
        parameters: [new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CustomerPayload')),
        responses: [
            new OA\Response(response: 200, description: 'Cliente atualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Cliente não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function update(): void {}

    #[OA\Delete(
        path: '/api/customers/{customer}', operationId: 'customersDestroy', summary: 'Excluir cliente',
        security: [['sanctum' => []]], tags: ['Customers'],
        parameters: [new OA\Parameter(name: 'customer', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Cliente removido', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Cliente não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function destroy(): void {}
}
