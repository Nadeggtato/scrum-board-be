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

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $developer;

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
        $this->project->members()->attach($this->projectManager->id);
    }

    public function test_developers_cant_create_project(): void
    {
        Sanctum::actingAs($this->developer);

        $this->postJson('api/projects', [
            'name' => fake()->word(),
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_invalid_payload_on_create_project_endpoint(): void
    {
        Sanctum::actingAs($this->projectManager);
        $data = [
            'name' => str_repeat('a', 256),
        ];

        $this->postJson('api/projects', $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_project(): void
    {
        Sanctum::actingAs($this->projectManager);
        $data = [
            'name' => fake()->word(),
        ];

        $response = $this->postJson('api/projects', $data)
            ->assertCreated()
            ->assertJsonPath('name', $data['name']);

        $this->assertDatabaseHas(Project::class, [
            ...$data,
            'creator_id' => $this->projectManager->id,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas(ProjectMember::class, [
            'user_id' => $this->projectManager->id,
            'project_id' => $response->json('id'),
        ]);
    }

    public function test_developers_cant_update_project()
    {
        Sanctum::actingAs($this->developer);

        $this->patchJson("api/projects/{$this->project->id}", [
            'name' => fake()->word(),
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_non_member_cant_update_project(): void
    {
        $nonMember = User::factory()->create();
        $this->projectManager->assignRole(Role::PROJECT_MANAGER);
        Sanctum::actingAs($nonMember);

        $this->patchJson("api/projects/{$this->project->id}", [
            'name' => fake()->word(),
        ])
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_invalid_payload_on_update_project_endpoint(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->patchJson("api/projects/{$this->project->id}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'is_active']);
    }

    public function test_update_project()
    {
        Sanctum::actingAs($this->projectManager);
        $newName = fake()->word();

        $response = $this->patchJson("api/projects/{$this->project->id}", [
            'name' => $newName,
            'is_active' => false,
        ])->assertOk()
            ->assertJsonPath('name', $newName);

        $this->assertDatabaseHas(Project::class, ['name' => $newName, 'is_active' => false]);
    }
}
