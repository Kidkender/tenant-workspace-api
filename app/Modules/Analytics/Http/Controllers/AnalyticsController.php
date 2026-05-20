<?php

namespace App\Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Analytics\Requests\AnalyticsRangeRequest;
use App\Modules\Analytics\Requests\TrendRequest;
use App\Modules\Analytics\Services\AnalyticsService;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analytics)
    {
    }

    public function completionRate(AnalyticsRangeRequest $request)
    {
        $tenant = app('tenant');
        $data = $this->analytics->completionRate($tenant->id, $request->toDateRange());

        return $this->success($data);
    }

    public function tasksPerUser(AnalyticsRangeRequest $request)
    {
        $tenant = app('tenant');
        $data = $this->analytics->taskPerUser($tenant->id, $request->toDateRange());

        return $this->success($data);
    }

    public function overdueRate()
    {
        $tenant = app('tenant');
        $data = $this->analytics->overdueRate($tenant->id);

        return $this->success($data);
    }

    public function priorityDistribution(AnalyticsRangeRequest $request)
    {
        $tenant = app('tenant');
        $data = $this->analytics->priorityDistribution($tenant->id, $request->toDateRange());

        return $this->success($data);
    }

    public function trend(TrendRequest $request)
    {
        $tenant = app('tenant');
        $data = $this->analytics->trend(
            $tenant->id,
            $request->toDateRange(),
            $request->granularity(),
            $request->metric()
        );

        return $this->success($data);
    }
}
