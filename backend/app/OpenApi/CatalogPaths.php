<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

final class CatalogPaths
{
    #[OA\Get(
        path: '/api/categories', operationId: 'categoriesIndex', summary: 'Listar categorias',
        security: [['sanctum' => []]], tags: ['Catalog'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Categorias listadas', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 401, description: 'Não autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 403, description: 'Acesso negado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function categoriesIndex(): void {}

    #[OA\Post(
        path: '/api/categories', operationId: 'categoriesStore', summary: 'Criar categoria',
        security: [['sanctum' => []]], tags: ['Catalog'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Categoria criada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function categoriesStore(): void {}

    #[OA\Patch(
        path: '/api/categories/{category}', operationId: 'categoriesUpdate', summary: 'Atualizar categoria',
        security: [['sanctum' => []]], tags: ['Catalog'],
        parameters: [new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/CategoryPayload')),
        responses: [
            new OA\Response(response: 200, description: 'Categoria atualizada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Categoria não encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function categoriesUpdate(): void {}

    #[OA\Delete(
        path: '/api/categories/{category}', operationId: 'categoriesDestroy', summary: 'Excluir categoria',
        security: [['sanctum' => []]], tags: ['Catalog'],
        parameters: [new OA\Parameter(name: 'category', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Categoria removida', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Categoria não encontrada', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 409, description: 'Categoria possui produtos vinculados', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function categoriesDestroy(): void {}

    #[OA\Get(
        path: '/api/products', operationId: 'productsIndex', summary: 'Listar produtos',
        security: [['sanctum' => []]], tags: ['Catalog'],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', schema: new OA\Schema(type: 'string', format: 'uuid')),
            new OA\Parameter(name: 'is_available', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'q', in: 'query', schema: new OA\Schema(type: 'string', maxLength: 150)),
            new OA\Parameter(name: 'page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1)),
            new OA\Parameter(name: 'per_page', in: 'query', schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 100)),
        ],
        responses: [new OA\Response(response: 200, description: 'Produtos listados', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function productsIndex(): void {}

    #[OA\Post(
        path: '/api/products', operationId: 'productsStore', summary: 'Criar produto',
        security: [['sanctum' => []]], tags: ['Catalog'],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductPayload')),
        responses: [
            new OA\Response(response: 201, description: 'Produto criado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function productsStore(): void {}

    #[OA\Get(
        path: '/api/products/{product}', operationId: 'productsShow', summary: 'Consultar produto',
        security: [['sanctum' => []]], tags: ['Catalog'],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Produto encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Produto não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function productsShow(): void {}

    #[OA\Patch(
        path: '/api/products/{product}', operationId: 'productsUpdate', summary: 'Atualizar produto',
        security: [['sanctum' => []]], tags: ['Catalog'],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(ref: '#/components/schemas/ProductPayload')),
        responses: [
            new OA\Response(response: 200, description: 'Produto atualizado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Produto não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 422, description: 'Dados inválidos', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function productsUpdate(): void {}

    #[OA\Delete(
        path: '/api/products/{product}', operationId: 'productsDestroy', summary: 'Excluir produto',
        security: [['sanctum' => []]], tags: ['Catalog'],
        parameters: [new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid'))],
        responses: [
            new OA\Response(response: 200, description: 'Produto removido', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 404, description: 'Produto não encontrado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function productsDestroy(): void {}
}
