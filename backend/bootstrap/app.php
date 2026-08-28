<?php

use App\Exceptions\BusinessConflictException;
use App\Http\Middleware\EnsureRole;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (BusinessConflictException $exception, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => $exception->getMessage(),
            ], 409);
        });

        $exceptions->render(function (AuthorizationException $exception, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => $exception->getMessage(),
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $exception, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Recurso não encontrado',
            ], 404);
        });

        $exceptions->render(function (AuthenticationException $exception, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Não autenticado',
            ], 401);
        });

        $exceptions->render(function (ValidationException $exception, $request) {
            if (! $request->expectsJson()) {
                return null;
            }

            return response()->json([
                'status' => 'error',
                'data' => null,
                'message' => 'Dados inválidos',
                'errors' => $exception->errors(),
            ], 422);
        });
    })->create();
