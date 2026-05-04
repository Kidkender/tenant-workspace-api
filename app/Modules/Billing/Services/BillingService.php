<?php

namespace App\Modules\Billing\Services;

use App\Modules\Task\Models\Task;

class BillingService
{
    public function __construct(private PlanService $planService) {}

    public function canCreateTask($tenantId): bool
    {
        $limit = $this->planService->getFeatureValueByTenant('task_limit', $tenantId);

        if ($limit === null) {
            return true;
        }

        $count = Task::where('tenant_id', $tenantId)->count();

        return $count < $limit;
    }
}
