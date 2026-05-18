<?php

namespace App\Modules\Access;

use App\Http\Controllers\Controller;
use App\Modules\Access\Models\Permission;
use App\Modules\Access\Models\Role;
use App\Modules\Access\Requests\CreateRoleRequest;
use App\Modules\Access\Requests\UpdateRoleRequest;
use App\Modules\Access\Services\RoleService;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __construct(private RoleService $roleService)
    {
    }

    public function index(): JsonResponse
    {
        $tenant = app('tenant');

        $roles = Role::where(fn($q) => $q->whereNull('tenant_id')->orWhere('tenant_id', $tenant->id))
            ->with('permissions')
            ->get();

        return $this->success($roles, [], 'Roles retrieved');
    }

    public function permissions(): JsonResponse
    {
        return $this->success(Permission::all(), [], 'Permissions retrieved');
    }

    public function store(CreateRoleRequest $request): JsonResponse
    {
        $tenant = app('tenant');
        $role = $this->roleService->createRole($tenant, $request->user(), $request->validated());

        return $this->success($role, [], 'Role created', 201);
    }

    public function update(UpdateRoleRequest $request, int $roleId): JsonResponse
    {
        $tenant = app('tenant');
        $role = Role::findOrFail($roleId);

        $updated = $this->roleService->updateRole($role, $tenant, $request->user(), $request->validated());

        return $this->success($updated, [], 'Role updated');
    }

    public function destroy(int $roleId): JsonResponse
    {
        $tenant = app('tenant');
        $role = Role::findOrFail($roleId);

        $this->roleService->deleteRole($role, $tenant);

        return $this->success(null, [], 'Role deleted');
    }
}
