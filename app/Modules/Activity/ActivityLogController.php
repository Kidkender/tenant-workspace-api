<?php

namespace App\Modules\Activity;

use App\Http\Controllers\Controller;
use App\Modules\Activity\Requests\ActivityLogFilterRequest;

class ActivityLogController extends Controller
{
    public function __construct(private ActivityLogService $activityLogService) {}

    public function index(ActivityLogFilterRequest $request)
    {
        $filters = $request->validated();
        $activityLogs = $this->activityLogService->getLogs($filters);

        return $this->success($activityLogs);
    }
}
