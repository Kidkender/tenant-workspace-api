<?php

namespace App\Modules\Tenant;

use App\Modules\Billing\Plan;
use App\Modules\Tenant\Models\TenantUser;
use App\Modules\User\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Fillable(['name', 'slug', 'plan_id', 'owner_user_id'])]
#[Table('tenants')]
class Tenant extends Model
{
    use HasUuids, HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
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
