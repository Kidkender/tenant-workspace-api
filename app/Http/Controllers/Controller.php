<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

abstract class Controller
{
    use AuthorizesRequests;

    protected function success(
        $data = null,
        array $meta = [],
        string $message = 'success',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'data' => $data,
            'meta' => $meta,
            'message' => $message,
        ], $status);
    }

    protected function error(
        string $message = 'error',
        int $status = 500
    ): JsonResponse {
        return response()->json([
            'message' => $message,
        ], $status);
    }
}
