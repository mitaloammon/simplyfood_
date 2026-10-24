<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || $request->user()->status !== 'ACTIVE') {
            return response()->json([
                'status' => 'error',
                'code' => 'ACCOUNT_INACTIVE',
                'data' => null,
                'message' => 'A conta não está ativa',
            ], 401);
        }

        return $next($request);
    }
}
