<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

final class DashboardPaths
{
    #[OA\Get(
        path: '/api/health', operationId: 'health', summary: 'Consultar saúde da API', tags: ['Dashboard'],
        responses: [new OA\Response(response: 200, description: 'API saudável', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope'))]
    )]
    public function health(): void {}

    #[OA\Get(
        path: '/api/dashboard/metrics', operationId: 'dashboardMetrics', summary: 'Consultar indicadores do dashboard',
        security: [['sanctum' => []]], tags: ['Dashboard'],
        responses: [
            new OA\Response(response: 200, description: 'Indicadores calculados', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 401, description: 'Não autenticado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
            new OA\Response(response: 403, description: 'Acesso negado', content: new OA\JsonContent(ref: '#/components/schemas/ApiEnvelope')),
        ]
    )]
    public function metrics(): void {}
}
