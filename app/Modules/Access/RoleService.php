<?php

namespace App\Modules\Access;

use App\Constants\ErrorCode;
use App\Modules\Access\Models\Permission;
use App\Modules\Access\Models\Role;
use App\Modules\Billing\Services\BillingService;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleService
{
    public function __construct(
        private BillingService $billingService,
        private PermissionService $permissionService,
    ) {
    }

    public function createRole(Tenant $tenant, User $actor, array $data): Role
    {
        if (!$this->billingService->canCreateRole($tenant->id)) {
            throw new AccessDeniedHttpException(ErrorCode::PERMISSION_DENIED);
        }

        $this->assertPermissionsSubset($actor, $tenant->id, $data['permissions']);

        $role = Role::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'tenant_id' => $tenant->id,
        ]);

        $role->permissions()->sync($data['permissions']);

        return $role->load('permissions');
    }


    public function updateRole(Role $role, Tenant $tenant, User $actor, array $data): Role
    {
        if (!$this->billingService->canCreateRole($tenant->id)) {
            throw new AccessDeniedHttpException(ErrorCode::PERMISSION_DENIED);
        }

        $this->assertNotSystemRole($role);
        $this->assertNotOwnerRole($role);
        $this->assertBelongsToTenant($role, $tenant);

        if (isset($data['permissions'])) {
            $this->assertPermissionsSubset($actor, $tenant->id, $data['permissions']);
            $role->permissions()->sync($data['permissions']);
            $this->permissionService->invalidateRoleCache($role->id);
        }

        $role->update(array_filter([
            'name' => $data['name'] ?? null,
            'description' => array_key_exists('description', $data) ? $data['description'] : $role->description,
        ], fn($v) => $v !== null));

        return $role->load('permissions');
    }

    public function deleteRole(Role $role, Tenant $tenant): void
    {
        $this->assertNotSystemRole($role);
        $this->assertNotOwnerRole($role);
        $this->assertBelongsToTenant($role, $tenant);

        $usageCount = TenantUser::where('role_id', $role->id)->count();

        if ($usageCount > 0) {
            throw new BadRequestHttpException(
                json_encode(['error' => ErrorCode::ROLE_IN_USE, 'count' => $usageCount])
            );
        }

        $role->delete();
    }

    private function assertBelongsToTenant(Role $role, Tenant $tenant): void
    {
        if ($role->tenant_id !== $tenant->id) {
            throw new NotFoundHttpException(ErrorCode::ROLE_NOT_FOUND);
        }
    }

    private function assertNotSystemRole(Role $role): void
    {
        if ($role->isSystemRole()) {
            throw new AccessDeniedHttpException(ErrorCode::ROLE_SYSTEM_PROTECTED);
        }
    }

    private function assertNotOwnerRole(Role $role): void
    {
        if ($role->isOwnerRole()) {
            throw new AccessDeniedHttpException(ErrorCode::ROLE_OWNER_PROTECTED);
        }
    }

    private function assertPermissionsSubset(User $actor, string $tenantId, array $requestedPermissionIds): void
    {
        $actorPermissionKeys = $this->permissionService->getPermissions($actor, $tenantId);

        $actorPermissionIds = Permission::whereIn('key', $actorPermissionKeys)
            ->pluck('id')
            ->map(fn($id) => (int) $id)
            ->toArray();

        $diff = array_diff($requestedPermissionIds, $actorPermissionIds);

        if (!empty($diff)) {
            throw new AccessDeniedHttpException(ErrorCode::PERMISSION_DENIED);
        }
    }
}
