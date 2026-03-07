<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectConfiguration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectConfiguration>
 */
class ProjectConfigurationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => ProjectConfiguration::TYPE_TASK_STATUSES,
            'value' => json_encode(ProjectConfiguration::DEFAULT_TASK_STATUSES),
            'project_id' => Project::factory()->create(['creator_id' => User::factory()->create()->id]),
        ];
    }
}
