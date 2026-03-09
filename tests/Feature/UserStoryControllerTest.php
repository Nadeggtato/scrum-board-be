<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Sprint;
use App\Models\UserStory;
use Laravel\Sanctum\Sanctum;

class UserStoryControllerTest extends BaseProjectTest
{
    private $userStory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userStory = UserStory::factory()->create(['project_id' => $this->project->id]);
    }

    public function test_non_members_cant_create_user_story(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->postJson(route('user_stories.store', [
            'project' => $this->project,
        ]), [
            'description' => fake()->text(),
            'story_points' => 1,
            'sprint_id' => null,
        ])->assertForbidden();
    }

    public function test_invalid_payload_returns_422_on_create_user_story_endpoint(): void
    {
        Sanctum::actingAs($this->developer);

        $this->postJson(route('user_stories.store', ['project' => $this->project]), [
            'description' => str_repeat('a', 1001),
            'story_points' => 'high',
            'sprint_id' => Sprint::factory()->create([
                'project_id' => Project::factory()->create(['creator_id' => $this->projectManager->id])->id,
            ]),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors([
                'description',
                'story_points',
                'sprint_id',
            ]);
    }

    public function test_members_can_create_user_story(): void
    {
        foreach ([$this->developer, $this->projectManager] as $key => $person) {
            Sanctum::actingAs($person);
            $data = [
                'description' => fake()->text(),
                'story_points' => 1,
                'sprint_id' => Sprint::factory()->create(['project_id' => $this->project->id])->id,
            ];

            $this->postJson(route('user_stories.store', [
                'project' => $this->project,
            ]), $data)
                ->assertCreated();

            $this->assertDatabaseCount(UserStory::class, $key + 2);
            $this->assertDatabaseHas(UserStory::class, [...$data, 'project_id' => $this->project->id]);
        }
    }

    public function test_non_member_cant_update_user_story(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->patchJson(route('user_stories.update', [
            'project' => $this->project,
            'userStory' => $this->userStory,
        ]))->assertForbidden();
    }

    public function test_invalid_payload_should_return_422_on_update_user_story(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->patchJson(route('user_stories.update', [
            'project' => $this->project,
            'userStory' => $this->userStory,
        ]), [
            'description' => str_repeat('a', 1005),
            'story_points' => 'test',
            'sprint_id' => Sprint::factory()->create(['project_id' => Project::factory()->create([
                'creator_id' => $this->projectManager->id,
            ])->id]),
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['description', 'story_points', 'sprint_id']);
    }

    public function test_update_user_story(): void
    {
        foreach ([$this->developer, $this->projectManager] as $person) {
            Sanctum::actingAs($person);

            $data = [
                'description' => fake()->text(),
                'story_points' => 1,
                'sprint_id' => Sprint::factory()->create(['project_id' => $this->project->id])->id,
            ];

            $this->patchJson(route('user_stories.update', [
                'project' => $this->project,
                'userStory' => $this->userStory,
            ]), $data)
                ->assertOk();

            $this->assertDatabaseCount(UserStory::class, 1);
            $this->assertDatabaseHas(UserStory::class, [...$data, 'id' => $this->userStory->id]);
        }
    }

    public function test_non_members_cant_delete_user_story(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->deleteJson(route('user_stories.delete', [
            'project' => $this->project,
            'userStory' => $this->userStory,
        ]))->assertForbidden();
    }

    public function test_delete_user_story_endpoint(): void
    {
        Sanctum::actingAs($this->developer);

        $this->deleteJson(route('user_stories.delete', [
            'project' => $this->project,
            'userStory' => $this->userStory,
        ]))->assertOk();

        $this->assertDatabaseCount(UserStory::class, 1);
        $this->assertSoftDeleted(UserStory::class, ['id' => $this->userStory->id]);

        Sanctum::actingAs($this->projectManager);
        $userStoryB = UserStory::factory()->create(['project_id' => $this->project->id]);

        $this->deleteJson(route('user_stories.delete', [
            'project' => $this->project,
            'userStory' => $userStoryB,
        ]))->assertOk();

        $this->assertDatabaseCount(UserStory::class, 2);
        $this->assertSoftDeleted(UserStory::class, ['id' => $userStoryB->id]);
    }
}
