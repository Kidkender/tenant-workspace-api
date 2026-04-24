<?php

namespace App\Modules\Access\Models;

use App\Modules\Tenant\Models\TenantUser;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Modules\Access\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TenantUser> $tenantUsers
 * @property-read int|null $tenant_users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @property string|null $tenant_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereTenantId($value)
 * @mixin \Eloquent
 */
#[Table('roles')]
#[Fillable('name', 'description', 'tenant_id')]
class Role extends Model
{
    use HasFactory;

    public $timestamps = false;

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function tenantUsers()
    {
        return $this->hasMany(TenantUser::class);
    }

}
