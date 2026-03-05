<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\UserStory;
use Laravel\Sanctum\Sanctum;

class TaskControllerTest extends BaseProjectTest
{
    public function test_non_member_cant_create_task(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->postJson(route('tasks.store', ['project' => $this->project]), [
            'description' => fake()->text(),
            'user_id' => $this->developer->id,
            'user_story_id' => UserStory::factory()->create(['project_id' => $this->project->id])->id,
        ])->assertForbidden();
    }

    public function test_invalid_payload_on_create_task_must_return_422(): void
    {
        Sanctum::actingAs($this->projectManager);
        $anotherProject = Project::factory()->create(['creator_id' => $this->projectManager]);

        $this->postJson(route('tasks.store', ['project' => $this->project]), [
            'description' => str_repeat('a', 1005),
            'user_id' => $this->nonMember->id,
            'user_story_id' => UserStory::factory()->create(['project_id' => $anotherProject])->id,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'description',
                'user_id',
                'user_story_id',
            ]);
    }

    public function test_developer_and_project_manager_can_create_task(): void
    {
        Sanctum::actingAs($this->projectManager);
        $data = [
            'title' => fake()->title(),
            'description' => fake()->text(),
            'user_id' => $this->developer->id,
            'user_story_id' => UserStory::factory()->create(['project_id' => $this->project->id])->id,
        ];

        $this->postJson(
            route('tasks.store', ['project' => $this->project]),
            $data
        )->assertCreated();

        $this->assertDatabaseCount(Task::class, 1);
        $this->assertDatabaseHas(Task::class, $data);
    }
}
