<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'SimplyFood API',
    description: 'API do PDV SimplyFood.'
)]
#[OA\Server(url: 'http://localhost:8080', description: 'Ambiente local')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Sanctum'
)]
#[OA\Tag(name: 'Auth', description: 'Autenticação e usuário autenticado')]
#[OA\Tag(name: 'Catalog', description: 'Categorias e produtos')]
#[OA\Tag(name: 'Customers', description: 'Clientes')]
#[OA\Tag(name: 'Cash', description: 'Caixa e movimentos')]
#[OA\Tag(name: 'Tables', description: 'Mesas e comandas')]
#[OA\Tag(name: 'Orders', description: 'Pedidos, itens e pagamentos')]
#[OA\Tag(name: 'Dashboard', description: 'Indicadores e saúde da aplicação')]
#[OA\Schema(
    schema: 'ApiEnvelope',
    required: ['status', 'data', 'message'],
    properties: [
        new OA\Property(property: 'status', type: 'string', enum: ['success', 'error'], example: 'success'),
        new OA\Property(property: 'data', type: 'object', nullable: true),
        new OA\Property(property: 'message', type: 'string', example: 'OK'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'LoginRequest',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email'),
        new OA\Property(property: 'password', type: 'string', format: 'password'),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CategoryPayload',
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 100),
        new OA\Property(property: 'sort_order', type: 'integer', minimum: 0, default: 0),
        new OA\Property(property: 'active', type: 'boolean', default: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'ProductPayload',
    properties: [
        new OA\Property(property: 'category_id', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', maxLength: 150),
        new OA\Property(property: 'description', type: 'string', nullable: true),
        new OA\Property(property: 'price', type: 'number', format: 'decimal', minimum: 0),
        new OA\Property(property: 'cost_price', type: 'number', format: 'decimal', minimum: 0),
        new OA\Property(property: 'sku', type: 'string', maxLength: 50, nullable: true),
        new OA\Property(property: 'is_available', type: 'boolean'),
        new OA\Property(property: 'preparation_time_minutes', type: 'integer', minimum: 0),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CustomerPayload',
    properties: [
        new OA\Property(property: 'name', type: 'string', minLength: 2, maxLength: 150),
        new OA\Property(property: 'phone', type: 'string', minLength: 8, maxLength: 20, nullable: true),
        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
        new OA\Property(property: 'document', type: 'string', maxLength: 30, nullable: true),
        new OA\Property(property: 'address', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'TablePayload',
    properties: [
        new OA\Property(property: 'number', type: 'integer', minimum: 1),
        new OA\Property(property: 'capacity', type: 'integer', minimum: 1),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'OrderItemPayload',
    required: ['product_id', 'quantity'],
    properties: [
        new OA\Property(property: 'product_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1),
        new OA\Property(property: 'notes', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'OrderPayload',
    required: ['order_type', 'items'],
    properties: [
        new OA\Property(property: 'order_type', type: 'string', enum: ['TABLE', 'COMMAND', 'COUNTER']),
        new OA\Property(property: 'table_id', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'command_id', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'customer_id', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'items', type: 'array', items: new OA\Items(ref: '#/components/schemas/OrderItemPayload')),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CashOpenPayload',
    required: ['cash_register_id', 'opening_balance'],
    properties: [
        new OA\Property(property: 'cash_register_id', type: 'string', format: 'uuid'),
        new OA\Property(property: 'opening_balance', type: 'number', format: 'decimal', minimum: 0),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CashMovementPayload',
    required: ['type', 'amount'],
    properties: [
        new OA\Property(property: 'type', type: 'string', enum: ['BLEED', 'SUPPLEMENT']),
        new OA\Property(property: 'amount', type: 'number', format: 'decimal', exclusiveMinimum: 0),
        new OA\Property(property: 'description', type: 'string', maxLength: 255, nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'CashClosePayload',
    required: ['closing_balance'],
    properties: [
        new OA\Property(property: 'closing_balance', type: 'number', format: 'decimal', minimum: 0),
        new OA\Property(property: 'notes', type: 'string', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'PaymentPayload',
    required: ['payment_method', 'amount'],
    properties: [
        new OA\Property(property: 'payment_method', type: 'string', enum: ['CASH', 'CREDIT_CARD', 'DEBIT_CARD', 'PIX', 'VOUCHER']),
        new OA\Property(property: 'amount', type: 'number', format: 'decimal', exclusiveMinimum: 0),
    ],
    type: 'object'
)]
final class OpenApiSpec
{
}
