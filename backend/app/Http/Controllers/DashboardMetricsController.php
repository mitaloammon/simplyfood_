<?php

namespace App\Http\Controllers;

use App\Application\Services\DashboardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardMetricsController extends Controller
{
    public function __construct(private readonly DashboardService $service)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            $dashboard = $this->service->buildUserDashboard($request->user());

            return response()->json([
                'status' => 'success',
                'data' => $dashboard,
                'message' => 'Metricas do dashboard carregadas com sucesso.',
            ], Response::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => 'Nao foi possivel carregar as metricas do dashboard.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}