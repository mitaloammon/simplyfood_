<?php

namespace App\Http\Middleware;

use App\Application\Readiness\EstablishmentReadiness;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstallationReady
{
    public function __construct(private readonly EstablishmentReadiness $readiness)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->readiness->isReady($request->user())) {
            return response()->json([
                'status' => 'error',
                'code' => 'ESTABLISHMENT_SETUP_REQUIRED',
                'data' => null,
                'message' => 'A configuração do estabelecimento está pendente',
            ], 409);
        }

        return $next($request);
    }
}
