<?php

namespace App\Modules\Billing\Models;

use App\Modules\Billing\Models\Subscription;
use App\Modules\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $price_monthly
 * @property int $price_yearly
 * @property int|null $max_users
 * @property array<array-key, mixed>|null $features
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereMaxUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan wherePriceMonthly($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Plan wherePriceYearly($value)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tenant> $tenants
 * @property-read int|null $tenants_count
 * @mixin \Eloquent
 */
#[Table('plans')]
#[Fillable('name', 'price_monthly', 'price_yearly', 'max_users', 'features')]
class Plan extends Model
{
    use HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            "features" => "array"
        ];
    }


    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

}
