<?php

namespace App\Http\Middleware;

use App\Constants\ErrorCode;
use App\Modules\Tenant\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenantId = $request->header('X-Tenant-ID');
        if (! $tenantId) {
            return response()->json(['error' => ErrorCode::TENANT_NOT_PROVIDED], 400);
        }

        $tenant = Tenant::find($tenantId);

        if (! $tenant) {
            return response()->json(['error' => ErrorCode::TENANT_NOT_FOUND], 404);
        }

        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
