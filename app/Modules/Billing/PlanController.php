<?php

namespace App\Modules\Billing;

use App\Http\Controllers\Controller;
use App\Modules\Billing\Services\PlanService;
use App\Modules\Billing\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function __construct(
        private PlanService $planService,
        private SubscriptionService $subscriptionService,
    ) {}

    public function index(): JsonResponse
    {
        $plans = $this->planService->getPlans();

        return $this->success($plans);
    }

    public function show(int $id): JsonResponse
    {
        $plan = $this->planService->getPlanWithFeatures($id);

        return $this->success($plan);
    }

    public function subscription(): JsonResponse
    {
        $tenant = app('tenant');
        $subscription = $this->subscriptionService->getActiveByTenant($tenant->id);

        return $this->success($subscription);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|integer|exists:plans,id',
        ]);

        $tenant = app('tenant');
        $subscription = $this->subscriptionService->subscribe($tenant->id, $request->input('plan_id'));

        return $this->success($subscription, [], 'Subscribed successfully', 201);
    }
}
