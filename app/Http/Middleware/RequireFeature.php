<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Common\Constants\ErrorCode;
use App\Modules\Billing\Services\BillingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireFeature
{
    public function __construct(private BillingService $billing) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = app('tenant');

        if (! $tenant || ! $this->billing->hasFeature($tenant->id, $feature)) {
            return response()->json(['error' => ErrorCode::FEATURE_NOT_AVAILABLE], 403);
        }

        return $next($request);
    }
}
