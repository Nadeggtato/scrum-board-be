<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectConfiguration;
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

    public function test_non_members_cant_update_task(): void
    {
        Sanctum::actingAs($this->nonMember);
        $data = [
            'title' => 'Updated title',
            'user_id' => $this->developer,
        ];

        $this->patchJson(route('tasks.update', [
            'project' => $this->project,
            'task' => $this->task,
        ]), $data)->assertForbidden();
        $this->assertDatabaseMissing(Task::class, [
            ...$data,
            'id' => $this->task->id,
        ]);
    }

    public function test_invalid_payload_returns_422_on_update_task(): void
    {
        Sanctum::actingAs($this->projectManager);
        $anotherProject = Project::factory()->create(['creator_id' => $this->projectManager]);
        ProjectConfiguration::factory()->create(['project_id' => $anotherProject->id]);
        ProjectConfiguration::factory()->create(['project_id' => $this->project->id]);
        $userStory = UserStory::factory()->create(['project_id' => $anotherProject->id]);

        $data = [
            'title' => str_repeat('a', 500),
            'description' => fake()->text(),
            'status' => 'Invalid Status',
            'user_id' => $this->nonMember,
            'user_story_id' => $anotherProject,
        ];

        $this->patchJson(route('tasks.update', [
            'project' => $this->project,
            'task' => $this->task,
        ]), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'status',
                'user_id',
                'user_story_id',
            ]);
    }

    public function test_members_can_update_task(): void
    {
        ProjectConfiguration::factory()->create(['project_id' => $this->project->id]);

        foreach ([$this->projectManager, $this->developer] as $key => $person) {
            Sanctum::actingAs($person);
            $userStory = UserStory::factory()->create(['project_id' => $this->project->id]);

            $data = [
                'title' => 'Updated title'.$key,
                'description' => fake()->text(),
                'status' => ProjectConfiguration::STATUS_IN_PROGRESS,
                'user_id' => $person->id,
                'user_story_id' => $userStory->id,
            ];

            $this->patchJson(route('tasks.update', [
                'project' => $this->project,
                'task' => $this->task,
            ]), $data)
                ->assertOk();

            $this->assertDatabaseCount(Task::class, 1);
            $this->assertDatabaseHas(Task::class, [...$data, 'id' => $this->task->id]);
        }
    }
}
