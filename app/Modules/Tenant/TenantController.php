<?php

namespace App\Modules\Tenant;

use App\Constants\ErrorCode;
use App\Http\Controllers\Controller;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\Tenant\Requests\InvitationRequest;
use App\Modules\Tenant\Requests\StoreTenantRequest;
use App\Modules\Tenant\Requests\UpdateTenantRequest;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(private TenantService $tenantService) {}

    public function store(StoreTenantRequest $request)
    {
        $user = $request->user();

        $tenant = $this->tenantService->createTenant(
            $request->validated(),
            $user
        );

        return $this->success($tenant, [], 'Tenant created', 201);
    }

    public function update(UpdateTenantRequest $request)
    {
        $tenant = app('tenant');

        if ($tenant->owner_user_id !== $request->user()->id) {
            throw new AuthorizationException(ErrorCode::PERMISSION_DENIED);
        }

        $updated = $this->tenantService->updateTenant($tenant, $request->validated());

        return $this->success($updated, [], 'Tenant updated');
    }

    public function members(Request $request)
    {
        $tenantId = $request->header('X-Tenant-ID');

        $members = TenantUser::with(['user:id,name,email', 'role:id,name'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get()
            ->map(fn (TenantUser $tu) => [
                'id' => $tu->user->id,
                'name' => $tu->user->name,
                'email' => $tu->user->email,
                'role' => $tu->role?->name,
            ]);

        return $this->success($members, [], 'Members retrieved');
    }

    public function removeMember(Request $request, string $userId)
    {
        $tenant = app('tenant');
        $this->tenantService->removeMember($tenant, $userId, $request->user());

        return $this->success(null, [], 'Member removed');
    }

    public function updateMemberRole(Request $request, string $userId)
    {
        $request->validate(['role_id' => 'required|integer|exists:roles,id']);

        $tenant = app('tenant');
        $this->tenantService->updateMemberRole($tenant, $userId, (int) $request->input('role_id'), $request->user());

        return $this->success(null, [], 'Role updated');
    }

    public function invite(InvitationRequest $request)
    {
        $tenant = app('tenant');
        $data = $request->validated();
        $this->tenantService->invite($tenant, $data['email'], $data['role_id']);

        return $this->success();
    }

    public function accept(Request $request, string $token)
    {
        $this->tenantService->accept($token, $request->user());

        return $this->success();
    }
}
