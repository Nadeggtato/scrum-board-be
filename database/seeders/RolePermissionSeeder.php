<?php

namespace Database\Seeders;

use App\Models\Role as RoleModel;
use Arr;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    private const PROJECT_PERMISSIONS = [
        'create-project',
        'view-project',
        'update-project',
    ];

    private const PROJECT_MEMBER_PERMISSIONS = [
        'add-project-member',
        'remove-project-member',
    ];

    private const ALL_PERMISSIONS = [
        self::PROJECT_PERMISSIONS,
        self::PROJECT_MEMBER_PERMISSIONS,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managerPermissions = [self::PROJECT_PERMISSIONS, self::PROJECT_MEMBER_PERMISSIONS];

        RoleModel::truncate();
        Permission::truncate();

        foreach (Arr::collapse(self::ALL_PERMISSIONS) as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach (RoleModel::ROLES as $role) {
            $createdRole = RoleModel::create(['name' => $role]);

            switch ($role) {
                case RoleModel::PROJECT_MANAGER:
                    $permissions = Permission::whereIn(
                        'name',
                        Arr::collapse($managerPermissions)
                    )->get();
                    $createdRole->syncPermissions($permissions);
                    break;
                case RoleModel::DEVELOPER:
                    break;
            }
        }
    }
}
