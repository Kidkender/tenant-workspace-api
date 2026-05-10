<?php

namespace App\Modules\Billing\Services;

use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\TenantUser;

class BillingService
{
    public function __construct(private PlanService $planService) {}

    public function canCreateTask(string $tenantId): bool
    {
        if (! $this->planService->canUseFeature('can_create_task', $tenantId)) {
            return false;
        }

        $limit = $this->planService->getFeatureValueByTenant('task_limit', $tenantId);

        if ($limit === null) {
            return true;
        }

        return Task::where('tenant_id', $tenantId)->count() < $limit;
    }

    public function canAddMember(string $tenantId): bool
    {
        $limit = $this->planService->getFeatureValueByTenant('member_limit', $tenantId);

        if ($limit === null) {
            return true;
        }

        return TenantUser::where('tenant_id', $tenantId)->count() < $limit;
    }
}
