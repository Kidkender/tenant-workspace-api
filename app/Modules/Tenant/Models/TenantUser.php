<?php

namespace App\Modules\Tenant\Models;

use App\Modules\Access\Models\Role;
use App\Modules\User\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $user_id
 * @property string|null $role_id
 * @property string $status
 * @property string|null $joined_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Role|null $role
 * @property-read User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereJoinedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TenantUser whereUserId($value)
 * @mixin \Eloquent
 */
#[Table('tenant_users')]
#[Fillable(['tenant_id', 'user_id', 'role_id', 'status', 'joined_at'])]
#[Hidden(['tenant_id', 'user_id'])]
class TenantUser extends Pivot
{
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
