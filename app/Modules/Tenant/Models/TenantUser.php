<?php

namespace App\Modules\Tenant\Models;

use App\Modules\Access\Models\Role;
use App\Modules\User\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

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
