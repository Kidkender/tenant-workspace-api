<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $query) {
            if ($tenant = app('tenant')) {
                $query->where($query->getModel()->getTable().'.tenant_id', $tenant->id);
            }
        });
    }
}
