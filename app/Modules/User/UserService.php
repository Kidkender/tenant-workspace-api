<?php

namespace App\Modules\User;

use App\Modules\Access\Models\Role;
use App\Modules\Access\Services\PermissionService;
use App\Modules\User\Models\User;
use App\Modules\User\Requests\UpdateUserRequest;

class UserService
{
    public function __construct(private PermissionService $permissionService)
    {
    }

    public function updateMe(User $user, UpdateUserRequest $data): array
    {
        $user->update(['name' => $data['name']]);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    public function getMe(User $user): array
    {
        $tenants = $user->tenants()->where('tenant_users.status', 'active')->get();

        $tenantData = $tenants->map(function ($tenant) use ($user) {
            $roleId = $tenant->pivot->role_id;
            $role = Role::find($roleId);
            $permissions = $this->permissionService->getPermissions($user, $tenant->id);

            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'slug' => $tenant->slug,
                'role' => $role?->name ?? 'member',
                'permissions' => $permissions,
            ];
        });

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'tenants' => $tenantData,
        ];
    }

    public function getByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();

    }

}
