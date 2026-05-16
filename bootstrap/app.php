<?php

use App\Constants\ErrorCode;
use App\Http\Middleware\CheckPermission;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__ . '/../routes/channels.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn() => null);
        $middleware->prepend(HandleCors::class);
        $middleware->preventRequestForgery(except: ['broadcasting/auth']);
        $middleware->alias([
            'permission' => CheckPermission::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(fn($request) => $request->is('api/*') || $request->expectsJson());

        $exceptions->render(fn(BadRequestHttpException $e) => response()->json([
            'message' => $e->getMessage() ?: ErrorCode::BAD_REQUEST,
            'error' => ErrorCode::BAD_REQUEST,
        ], 400));

        $exceptions->render(fn(AuthenticationException $e) => response()->json([
            'error' => ErrorCode::AUTH_UNAUTHORIZED
        ], 401));

        $exceptions->render(fn(AccessDeniedHttpException $e) => response()->json([
            'error' => $e->getMessage() ?: ErrorCode::AUTH_FORBIDDEN,
        ], 403));

        $exceptions->render(fn(AuthorizationException $e) => response()->json([
            'error' => ErrorCode::AUTH_FORBIDDEN,
        ], 403));

        $exceptions->render(fn(ModelNotFoundException $e) => response()->json([
            'error' => ErrorCode::RESOURCE_NOT_FOUND,
        ], 404));

        $exceptions->render(fn(NotFoundHttpException $e) => response()->json([
            'error' => $e->getMessage() ?: ErrorCode::RESOURCE_NOT_FOUND,
        ], 404));

        $exceptions->render(fn(ValidationException $e) => response()->json([
            'error' => ErrorCode::VALIDATION_FAILED,
            'errors' => $e->errors(),
        ], 422));


        $exceptions->render(fn(ThrottleRequestsException $e) => response()->json([
            'error' => ErrorCode::TOO_MANY_REQUESTS,
        ], 429));


        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }

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
