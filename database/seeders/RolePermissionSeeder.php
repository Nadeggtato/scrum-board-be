<?php

namespace Database\Seeders;

use Arr;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    private const PROJECT_PERMISSIONS = [
        'create-project',
        'view-project',
        'update-project',
        'delete-project',
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
        $roles = ['Super Admin', 'Project Manager', 'Developer'];
        $managerPermissions = [self::PROJECT_PERMISSIONS, self::PROJECT_MEMBER_PERMISSIONS];

        Role::truncate();
        Permission::truncate();

        foreach (Arr::collapse(self::ALL_PERMISSIONS) as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach ($roles as $role) {
            $createdRole = Role::create(['name' => $role]);

            switch ($role) {
                case 'Project Manager':
                    $permissions = Permission::whereIn(
                        'name',
                        Arr::collapse($managerPermissions)
                    )->get();
                    $createdRole->syncPermissions($permissions);
                    break;
                case 'Developer':
                    break;
            }
        }
    }
}
