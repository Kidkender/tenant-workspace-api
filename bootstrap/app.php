<?php

use App\Constants\ErrorCode;
use App\Http\Middleware\CheckPermission;
use Cassandra\Exception\AuthenticationException;
use Cassandra\Exception\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn() => null);
        $middleware->alias([
            'permission' => CheckPermission::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn($request) => $request->is('api/*'));

        $exceptions->render(function (AuthenticationException $e) {
            return response()->json([
                'error' => ErrorCode::AUTH_UNAUTHORIZED
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'error' => ErrorCode::AUTH_FORBIDDEN,
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'resource.not_found',
            ], 404);
        });

        $exceptions->render(function (ValidationException $e) {
            return response()->json([
                'error' => 'validation.failed',
                'errors' => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (\Throwable $e) {
            \Log::error('Server error: ', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(3),
            ]);

            return response()->json([
                'error' => ErrorCode::INTERNAL_SERVER_ERROR,
            ], 500);
        });
    })->create();
