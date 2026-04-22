<?php

namespace App\Http\Middleware;

use App\Modules\Access\PermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{

    public function __construct(
        private PermissionService $permissionService
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $user = $request->user();
        $tenant = app('tenant');
        if (!$user || !$tenant) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $permissionList = explode('|', $permissions);

        foreach ($permissionList as $permission) {
            if ($this->permissionService->hasPermission($user, $permission, $tenant->id)) {
                return next($request);
            }
        }

        return response()->json([
            "message" => "Forbidden"
        ], 403);
    }
}
