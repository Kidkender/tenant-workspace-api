<?php

namespace App\Modules\Tenant;

use App\Http\Controllers\Controller;
use App\Modules\Tenant\Requests\StoreTenantRequest;

class TenantController extends Controller
{
    public function __construct(private TenantService $tenantService)
    {
    }

    public function store(StoreTenantRequest $request)
    {
        $user = $request->user();

        $tenant = $this->tenantService->createTenant(
            $request->validated(),
            $user
        );

        return $this->success($tenant, [], 'Tenant created', 201);
    }
}
