<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Support\ProjectMemberChecker;

class ProjectMemberPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->can('add-project-member') &&
            app(ProjectMemberChecker::class)($project, $user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->can('remove-project-member') &&
            app(ProjectMemberChecker::class)($project, $user);
    }
}
