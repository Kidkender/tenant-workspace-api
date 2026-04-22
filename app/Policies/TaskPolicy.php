<?php

namespace App\Policies;

use App\Modules\Tenant\Models\Tenant;
use App\Modules\User\Models\User;

class TaskPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user, Tenant $tenant)
    {
        return $user->isAdmin($tenant->id);
    }
}
