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
            PermissionConstant::TASK_CREATE,
            PermissionConstant::TASK_UPDATE,
        ];

        foreach ($permissions as $key) {
            Permission::updateOrCreate(["key" => $key]);
        }

        $admin = Role::firstOrCreate(["name" => "admin"]);
        $member = Role::firstOrCreate(["name" => "member"]);

        $admin->permissions()->sync(
            Permission::pluck("id")
        );

        $member->permissions()->sync(
            Permission::whereIn('key', [
                PermissionConstant::TASK_CREATE,
                PermissionConstant::TASK_UPDATE,
            ])->pluck('id')
        );


    }
}
