<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Models\ProjectConfiguration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProjectService
{
    public function execute(User $user, array $data)
    {
        return DB::transaction(function () use ($user, $data): Project {
            $userId = $user->id;
            $project = Project::create([
                ...$data,
                'creator_id' => $userId,
            ]);

            ProjectConfiguration::create([
                'type' => 'task_statuses',
                'value' => json_encode(ProjectConfiguration::DEFAULT_TASK_STATUSES),
                'project_id' => $project->id,
            ]);

            $project->members()->attach($userId);

            return $project;
        });
    }
}
