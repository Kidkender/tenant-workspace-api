<?php

namespace App\Modules\Tenant\Models;

use App\Modules\Billing\Models\Plan;
use App\Modules\Billing\Models\Subscription;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string $owner_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TenantUser> $tenantUsers
 * @property-read int|null $tenant_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereOwnerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereUpdatedAt($value)
 * @property-read Plan|null $plan
 * @property-read Subscription|null $subscription
 * @property string $status
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tenant whereStatus($value)
 * @mixin \Eloquent
 */
#[Fillable(['name', 'slug', 'plan_id', 'status', 'owner_user_id'])]
#[Table('tenants')]
class Tenant extends Model
{
    use HasUuids, HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_users')
            ->withPivot(['role_id', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function tenantUsers()
    {
        return $this->hasMany(TenantUser::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class, 'tenant_id');
    }

    public function hasUser(string $userId): bool
    {
        return $this->users()->where('user_id', $userId)->exists();
    }

    public function getUserRole(string $userId): ?string
    {
        $user = $this->users()->where('user_id', $userId)->first();
        return $user ? $user->pivot->role_id : null;
    }

}
