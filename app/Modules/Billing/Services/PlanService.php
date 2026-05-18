<?php

namespace App\Modules\Billing\Services;

use App\Common\Constants\ErrorCode;
use App\Common\Constants\Feature;
use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\PlanFeature;
use App\Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PlanService
{
    public function __construct(private SubscriptionService $subscriptionService) {}

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
        $planFeature = PlanFeature::where('plan_id', $planId)
            ->where('feature_key', $featureKey)
            ->first();

        if (! $planFeature) {
            throw new NotFoundHttpException(ErrorCode::FEATURE_NOT_FOUND);
        }

        return $planFeature;
    }

    public function getFeatureValueByTenant(string $featureKey, string $tenantId)
    {
        $subscription = Subscription::where('status', 'active')
            ->where('tenant_id', $tenantId)->first();

        if (! $subscription) {
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
            Feature::TASK_LIMIT, Feature::MEMBER_LIMIT, Feature::TENANT_LIMIT, Feature::STORAGE_MB => (int) $value,
            Feature::CAN_CREATE_TASK, Feature::ACTIVITY_LOG, Feature::EMAIL_INVITE,
            Feature::CUSTOM_ROLES, Feature::ANALYTICS, Feature::REALTIME => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }

    public function getMaxUploadSize($tenantId): ?int
    {
        $subscription = $this->subscriptionService->getActiveByTenant($tenantId);

        return $subscription?->plan->features()
            ->where('feature_key', 'storage_mb')->value('value');
    }

    public function getMaxLimitTenantByOwner(string $userId): int
    {
        $maxLimit = Subscription::query()
            ->where('status', 'active')
            ->whereHas('tenant', fn ($q) => $q->where('owner_user_id', $userId))
            ->join('plan_features', 'plan_features.plan_id', '=', 'subscriptions.plan_id')
            ->where('plan_features.feature_key', Feature::TENANT_LIMIT)
            ->max(DB::raw('CAST(plan_features.value AS UNSIGNED)'));

        if ($maxLimit) {
            return $maxLimit;
        }

        $freePlan = $this->getFreePlan();
        $freeLimit = PlanFeature::where('plan_id', $freePlan->id)
            ->where('feature_key', Feature::TENANT_LIMIT)
            ->value('value');

        return $freeLimit ? (int) $freeLimit : 3;
    }
}
