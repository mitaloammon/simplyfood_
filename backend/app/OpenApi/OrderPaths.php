<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

final class OrderPaths
{
    #[OA\Get(
        path: '/api/orders', operationId: 'ordersIndex', summary: 'Listar pedidos',
        security: [['sanctum' => []]], tags: ['Orders'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string', enum: ['OPEN', 'IN_PREPARATION', 'READY', 'DELIVERED', 'CLOSED', 'CANCELLED'])),
            new OA\Parameter(name: 'table_id', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'command_id', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [new OA\Response(response: 200, description: 'Pedidos listados', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function index(): void {}

    #[OA\Post(
        path: '/api/orders', operationId: 'ordersStore', summary: 'Criar pedido',
        security: [['sanctum' => []]], tags: ['Orders'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/OrderPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Pedido criado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Conflito ao criar pedido', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function store(): void {}

    #[OA\Get(
        path: '/api/orders/{order}', operationId: 'ordersShow', summary: 'Consultar pedido',
        security: [['sanctum' => []]], tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Pedido encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Pedido não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function show(): void {}

    #[OA\Post(
        path: '/api/orders/{order}/items', operationId: 'ordersAddItem', summary: 'Adicionar item ao pedido',
        security: [['sanctum' => []]], tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/OrderItemPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Item adicionado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Pedido não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Pedido não aceita alterações', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function addItem(): void {}

    #[OA\Patch(
        path: '/api/orders/{order}/status', operationId: 'ordersUpdateStatus', summary: 'Atualizar status do pedido',
        security: [['sanctum' => []]], tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['status'], properties: [new OA\Property(property: 'status', type: 'string', enum: ['IN_PREPARATION', 'READY', 'DELIVERED', 'CLOSED', 'CANCELLED'])]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Status atualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Transição de status inválida', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function updateStatus(): void {}

    #[OA\Delete(
        path: '/api/orders/{order}/items/{item}', operationId: 'ordersRemoveItem', summary: 'Remover item do pedido',
        security: [['sanctum' => []]], tags: ['Orders'],
        parameters: [
            new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'item', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Item removido', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Pedido ou item não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Pedido não aceita alterações', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function removeItem(): void {}

    #[OA\Post(
        path: '/api/orders/{order}/payments', operationId: 'paymentsStore', summary: 'Registrar pagamento do pedido',
        security: [['sanctum' => []]], tags: ['Orders'],
        parameters: [new OA\Parameter(name: 'order', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/PaymentPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Pagamento registrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Pedido não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Pagamento não permitido', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function payment(): void {}
}
