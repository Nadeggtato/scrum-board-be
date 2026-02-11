<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\User;
use App\Support\ProjectMemberChecker;

class SprintPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project, Sprint $sprint): bool
    {
        return $user->can('view-sprints') &&
            app(ProjectMemberChecker::class)($project, $user) &&
            $project->id === $sprint->project_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->can('create-sprint') &&
            app(ProjectMemberChecker::class)($project, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project, Sprint $sprint): bool
    {
        return $user->can('update-sprint') &&
            app(ProjectMemberChecker::class)($project, $user) &&
            $project->id === $sprint->project_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sprint $sprint): bool
    {
        return false;
    }
}
