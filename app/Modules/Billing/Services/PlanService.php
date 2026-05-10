<?php

namespace App\Modules\Billing\Services;

use App\Constants\ErrorCode;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\PlanFeature;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlanService
{
    public function __construct()
    {
    }

    public function getPlans()
    {
        return Plan::with('features')->get();
    }

    public function getPlanWithFeatures(int $id): Plan
    {
        return Plan::with('features')->findOrFail($id);
    }

    public function getFreePlan()
    {
        return Plan::where('price_monthly', 0)->first();
    }

    public function getPlanFeatures($planId)
    {
        return PlanFeature::where('plan_id', $planId)->get();
    }

    public function getFeature($planId, $featureKey): PlanFeature
    {
        Log::info('planId: ' . $planId . ', featureKey: ' . $featureKey);

        $planFeature = PlanFeature::where('plan_id', $planId)
            ->where('feature_key', $featureKey)
            ->first();

        if (!$planFeature) {
            throw new NotFoundHttpException(ErrorCode::FEATURE_NOT_FOUND);
        }

        return $planFeature;
    }

    public function getFeatureValueByTenant(string $featureKey, string $tenantId)
    {
        $subscription = Subscription::where('status', 'active')
            ->where('tenant_id', $tenantId)->first();

        if (!$subscription) {
            return null;
        }

        $feature = $this->getFeature($subscription->plan_id, $featureKey);

        return $this->castValue($featureKey, $feature->value);
    }

    public function canUseFeature(string $featureKey, string $tenantId): bool
    {
        $value = $this->getFeatureValueByTenant($featureKey, $tenantId);

        if ($value === null) {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value > 0;
        }

        return true;
    }

    private function castValue(string $key, ?string $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($key) {
            'task_limit', 'member_limit' => (int) $value,
            'can_create_task' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }
}
