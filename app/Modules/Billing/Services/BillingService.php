<?php

namespace App\Modules\Billing\Services;

use App\Constants\Feature;
use App\Modules\Task\Models\Task;
use App\Modules\Tenant\Models\TenantUser;

class BillingService
{
    public function __construct(private PlanService $planService) {}

    public function canCreateTask(string $tenantId): bool
    {
        if (! $this->planService->canUseFeature(Feature::CAN_CREATE_TASK, $tenantId)) {
            return false;
        }

        $limit = $this->planService->getFeatureValueByTenant(Feature::TASK_LIMIT, $tenantId);

        if ($limit === null) {
            return true;
        }

        return Task::where('tenant_id', $tenantId)->count() < $limit;
    }

    public function canAddMember(string $tenantId): bool
    {
        $limit = $this->planService->getFeatureValueByTenant(Feature::MEMBER_LIMIT, $tenantId);

        if ($limit === null) {
            return true;
        }

        return TenantUser::where('tenant_id', $tenantId)->count() < $limit;
    }
}
