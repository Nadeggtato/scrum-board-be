<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;

class ProjectMemberChecker
{
    public function __invoke(Project $project, User $user): bool
    {
        return $project->members()->whereKey($user->id)->exists();
    }
}
