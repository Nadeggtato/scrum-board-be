<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Models\UserStory;
use App\Support\ProjectMemberChecker;

class UserStoryPolicy
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
    public function view(User $user, Project $project, UserStory $userStory): bool
    {
        return $user->can('create-user-story') &&
            app(ProjectMemberChecker::class)($project, $user) &&
            $project->id === $userStory->project_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Project $project): bool
    {
        return $user->can('create-user-story') &&
            app(ProjectMemberChecker::class)($project, $user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project, UserStory $userStory): bool
    {
        return $user->can('update-user-story') &&
            app(ProjectMemberChecker::class)($project, $user) &&
            $userStory->project_id === $project->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project, UserStory $userStory): bool
    {
        return $user->can('delete-user-story') &&
            app(ProjectMemberChecker::class)($project, $user) &&
            $userStory->project_id === $project->id;
    }
}
