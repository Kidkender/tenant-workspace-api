<?php

namespace App\Modules\Activity;

use App\Http\Controllers\Controller;
use App\Modules\Activity;
use App\Modules\Activity\Requests\ActivityLogFilterRequest;
use App\Modules\Activity\ActivityLogService;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct(private ActivityLogService $activityLogService)
    {
    }

    public function index(ActivityLogFilterRequest $request)
    {
        $filters = $request->validated();
        $activityLogs = $this->activityLogService->getLogs($filters);
        return response()->json($activityLogs);
    }


}
