<?php

namespace App\Modules\Billing\Services;

use App\Modules\Billing\Models\Subscription;

class SubscriptionService
{
    public function __construct() {}

    public function getActiveByTenant($tenantId): ?Subscription
    {
        return Subscription::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with('plan.features')
            ->first();
    }

    public function subscribe($tenantId, $planId): Subscription
    {
        Subscription::where('tenant_id', $tenantId)->update(['status' => 'inactive']);

        $subscription = Subscription::create([
            'tenant_id' => $tenantId,
            'plan_id' => $planId,
            'status' => 'active',
            'started_at' => now(),
            'expired_at' => now()->addMonth(),
        ]);

        return $subscription->load('plan.features');
    }
}
