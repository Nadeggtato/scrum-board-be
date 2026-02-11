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

    private const SPRINT_PERMISSIONS = [
        'view-sprints',
        'create-sprint',
        'update-sprint',
    ];

    private const USER_STORY_PERMISSIONS = [
        'view-user-stories',
        'create-user-story',
        'update-user-story',
        'delete-user-story',
    ];

    private const ALL_PERMISSIONS = [
        self::PROJECT_PERMISSIONS,
        self::PROJECT_MEMBER_PERMISSIONS,
        self::SPRINT_PERMISSIONS,
        self::USER_STORY_PERMISSIONS,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissions = Arr::collapse(self::ALL_PERMISSIONS);
        $devPermissions = [self::USER_STORY_PERMISSIONS];

        RoleModel::truncate();
        Permission::truncate();

        foreach ($allPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach (RoleModel::ROLES as $role) {
            $createdRole = RoleModel::create(['name' => $role]);

            switch ($role) {
                case RoleModel::PROJECT_MANAGER:
                    $permissions = Permission::whereIn(
                        'name',
                        $allPermissions
                    )->get();

                    break;
                case RoleModel::DEVELOPER:
                    $permissions = Permission::whereIn(
                        'name',
                        Arr::collapse($devPermissions)
                    )->get();

                    break;
            }

            if (isset($permissions)) {
                $createdRole->syncPermissions($permissions);
            }
        }
    }
}
