<?php

namespace Database\Seeders;

use App\Constants\Permission as PermissionConstant;
use App\Modules\Access\Models\Permission;
use App\Modules\Access\Models\Role;
use Illuminate\Database\Seeder;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            PermissionConstant::TASK_VIEW,
            PermissionConstant::TASK_CREATE,
            PermissionConstant::TASK_UPDATE,
            PermissionConstant::TASK_DELETE,
            PermissionConstant::COMMENT_CREATE,
            PermissionConstant::COMMENT_DELETE,
            PermissionConstant::COMMENT_DELETE_ANY,
            PermissionConstant::ATTACHMENT_CREATE,
            PermissionConstant::ATTACHMENT_DELETE,
            PermissionConstant::ATTACHMENT_DELETE_ANY,
        ];

        foreach ($permissions as $key) {
            Permission::updateOrCreate(['key' => $key]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $member = Role::firstOrCreate(['name' => 'member']);
        $owner = Role::firstOrCreate(['name' => 'owner']);

        $allPermissions = Permission::pluck('id');

        $admin->permissions()->sync($allPermissions);
        $owner->permissions()->sync($allPermissions);

        $member->permissions()->sync(
            Permission::whereIn('key', [
                PermissionConstant::TASK_VIEW,
                PermissionConstant::TASK_CREATE,
                PermissionConstant::TASK_UPDATE,
                PermissionConstant::COMMENT_CREATE,
                PermissionConstant::COMMENT_DELETE,
                PermissionConstant::ATTACHMENT_CREATE,
                PermissionConstant::ATTACHMENT_DELETE,
            ])->pluck('id')
        );

        \Illuminate\Support\Facades\Cache::flush();
    }
}
