<?php

namespace App\Http\Controllers;

use App\Application\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $service)
    {
    }

    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        $dashboard = $this->service->buildUserDashboard($user);

        return Inertia::render('Dashboard', [
            'user' => $dashboard['user'],
            'metrics' => $dashboard['metrics'],
        ]);
    }
}
