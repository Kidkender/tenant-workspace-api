<?php
namespace App\Modules\Access;

use App\Modules\Access\Models\Role;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\Models\User;
use Illuminate\Support\Facades\Cache;
use function in_array;

class PermissionService
{
    public function __construct()
    {
    }

    public function getPermissions(User $user, string $tenantId)
    {

        return Cache::remember(
            "perm:{$user->id}:{$tenantId}",
            3600,
            function () use ($user, $tenantId) {
                $tenantUser = TenantUser::where('tenant_id', $tenantId)->where('user_id', $user->id)->first();
                if (!$tenantUser) {
                    return [];
                }

                $roleId = $tenantUser->role_id;

                $permissions = Role::find($roleId)->permissions()->pluck('key')->toArray();
                return $permissions;

            }
        );
    }

    public function hasPermission(User $user, string $permissionKey, $tenantId): bool
    {
        $cacheKey = "user:{$user->id}:tenant:{$tenantId}:permissions";

        $permissions = cache()->remember($cacheKey, 300, function () use ($user, $tenantId) {

            $tenant = $user->tenants()->where('tenant_id', $tenantId)->first();

            if (!$tenant) {
                return false;
            }
            return $this->getPermissions($user, $tenantId);
        });
        return in_array($permissionKey, $permissions);
    }

    public function invalidateRoleCache(int $roleId): void
    {
        TenantUser::where('role_id', $roleId)
            ->get()
            ->each(function (TenantUser $tu) {
                Cache::forget("perm:{$tu->user_id}:{$tu->tenant_id}");
                Cache::forget("user:{$tu->user_id}:tenant:{$tu->tenant_id}:permissions");
            });
    }
}
