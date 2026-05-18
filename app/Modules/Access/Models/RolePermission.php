<?php

namespace App\Modules\Access\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $role_id
 * @property int $permission_id
 * @property-read \App\Modules\Access\Models\Permission $permission
 * @property-read \App\Modules\Access\Models\Role $role
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission whereRoleId($value)
 * @mixin \Eloquent
 */
#[Table('role_permissions')]
#[Fillable('role_id', 'permission_id')]
class RolePermission extends Pivot
{
    use HasFactory, HasUuids;

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }
}
