<?php

namespace App\Modules\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\Tenant\Requests\InvitationRequest;
use App\Modules\Tenant\Requests\StoreTenantRequest;
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

    public function members(Request $request)
    {
        $tenantId = $request->header('X-Tenant-ID');

        $members = TenantUser::with('user:id,name,email')
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->get()
            ->map(fn (TenantUser $tu) => [
                'id' => $tu->user->id,
                'name' => $tu->user->name,
                'email' => $tu->user->email,
            ]);

        return $this->success($members, [], 'Members retrieved');
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
