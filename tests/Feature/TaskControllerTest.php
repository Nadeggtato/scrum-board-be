<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\UserStory;
use Laravel\Sanctum\Sanctum;

class TaskControllerTest extends BaseProjectTest
{
    private $task;

    private $userStory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userStory = UserStory::factory()->create(['project_id' => $this->project->id]);
        $this->task = Task::factory()->create(['user_story_id' => $this->userStory->id]);
    }

    public function test_non_member_cant_create_task(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->postJson(route('tasks.store', ['project' => $this->project]), [
            'description' => fake()->text(),
            'user_id' => $this->developer->id,
            'user_story_id' => $this->userStory->id,
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
        $taskCount = 1;

        foreach ([$this->projectManager, $this->developer] as $person) {
            Sanctum::actingAs($person);
            $data = [
                'title' => fake()->title(),
                'description' => fake()->text(),
                'user_id' => $this->developer->id,
                'user_story_id' => $this->userStory->id,
            ];

            $this->postJson(
                route('tasks.store', ['project' => $this->project]),
                $data
            )->assertCreated();

            $taskCount++;
            $this->assertDatabaseCount(Task::class, $taskCount);
            $this->assertDatabaseHas(Task::class, $data);
        }
    }

    public function test_non_member_cant_view_task(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->getJson(route('tasks.show', [
            'project' => $this->project,
            'task' => $this->task,
        ]))
            ->assertForbidden();
    }

    public function test_members_can_view_task(): void
    {
        foreach ([$this->projectManager, $this->developer] as $person) {
            Sanctum::actingAs($person);

            $this->getJson(route('tasks.show', [
                'project' => $this->project,
                'task' => $this->task,
                'include' => 'assignee,userStory',
            ]))->assertOk()
                ->assertJsonStructure([
                    'id',
                    'title',
                    'description',
                    'assignee',
                    'user_story',
                ]);
        }
    }
}
