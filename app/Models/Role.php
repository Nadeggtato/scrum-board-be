<?php

namespace App\Models;

class Role extends \Spatie\Permission\Models\Role
{
    public const SUPER_ADMIN = 'Super Admin';

    public const PROJECT_MANAGER = 'Project Manager';

    public const DEVELOPER = 'Developer';

    public const ROLES = [
        self::SUPER_ADMIN,
        self::PROJECT_MANAGER,
        self::DEVELOPER,
    ];
}
