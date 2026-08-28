<?php

namespace App\Http\Controllers\Api;

use App\Application\Dashboard\DashboardMetricsService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardMetricsService $metrics)
    {
    }

    public function metrics(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->metrics->get($request->user()),
            'message' => 'OK',
        ]);
    }
}
