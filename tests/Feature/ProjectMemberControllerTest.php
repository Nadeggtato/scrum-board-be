<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class ProjectMemberControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $developer;

    private User $nonMember;

    private User $projectManager;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->developer = User::factory()->create();
        $this->developer->assignRole(Role::DEVELOPER);

        $this->projectManager = User::factory()->create();
        $this->projectManager->assignRole(Role::PROJECT_MANAGER);

        $this->project = Project::factory()->create(['creator_id' => $this->projectManager->id]);
        $this->project->members()->sync([$this->projectManager->id, $this->developer->id]);

        $this->nonMember = User::factory()->create();
        $this->nonMember->assignRole(Role::PROJECT_MANAGER);
    }

    public function test_non_member_cant_add_member_to_project(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->postJson(
            route(
                'members.store',
                ['project' => $this->project, 'user_ids' => [$this->developer->id]],
            )
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_developer_cant_add_member_to_project(): void
    {
        Sanctum::actingAs($this->developer);

        $this->postJson(
            route(
                'members.store',
                ['project' => $this->project, 'user_ids' => [$this->developer->id]],
            )
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_invalid_payload_on_add_existing_member_endpoint_should_return_422(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->postJson(
            route(
                'members.store',
                ['project' => $this->project, 'user_ids' => [fake()->uuid()]],
            )
        )->assertUnprocessable();
    }

    public function test_adding_existing_member_should_not_cause_duplicates(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->postJson(
            route(
                'members.store',
                ['project' => $this->project, 'user_ids' => [$this->developer->id]],
            )
        )->assertCreated()
            ->assertJsonStructure([
                'id',
                'name',
                'members',
            ]);
        $this->assertDatabaseCount(ProjectMember::class, 2);
    }

    public function test_add_member_to_project_endpoint(): void
    {
        Sanctum::actingAs($this->projectManager);
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->postJson(
            route(
                'members.store',
                ['project' => $this->project, 'user_ids' => [$userA->id, $userB->id]],
            )
        )->assertCreated()
            ->assertJsonStructure([
                'id',
                'name',
                'members',
            ]);

        $this->assertDatabaseCount(ProjectMember::class, 4);

        foreach ([$userA, $userB] as $user) {
            $this->assertDatabaseHas('project_members', [
                'project_id' => $this->project->id,
                'user_id' => $user->id,
            ]);
        }
    }

    public function test_developer_cant_remove_project_member(): void
    {
        Sanctum::actingAs($this->developer);

        $this->deleteJson(route(
            'members.delete',
            ['project' => $this->project, 'user' => $this->developer])
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_non_member_cant_remove_project_member(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->deleteJson(route(
            'members.delete',
            ['project' => $this->project, 'user' => $this->developer])
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_remove_member_to_project_endpoint(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->deleteJson(route(
            'members.delete',
            ['project' => $this->project, 'user' => $this->developer])
        )->assertOk();

        $this->assertDatabaseCount(ProjectMember::class, 2);
        $this->assertDatabaseMissing(
            ProjectMember::class,
            ['user_id' => $this->developer->id, 'deleted_at' => null]
        );

        $this->deleteJson(route(
            'members.delete',
            ['project' => $this->project, 'user' => $this->developer])
        )->assertUnprocessable();
    }
}
