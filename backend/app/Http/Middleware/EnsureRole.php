<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    private const ROLES = ['ADMIN', 'MANAGER', 'CASHIER', 'WAITER', 'KITCHEN'];

    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $allowedRoles = array_intersect($roles, self::ROLES);

        if (! $user || ! in_array($user->role, $allowedRoles, true)) {
            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Sem permissão para o papel exigido',
            ], 403);
        }

        return $next($request);
    }
}
