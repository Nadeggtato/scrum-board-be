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

    protected function setUp(): void
    {
        parent::setUp();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->seed(RolePermissionSeeder::class);

        $this->developer = User::factory()->create();
        $this->developer->assignRole(Role::DEVELOPER);

        $this->projectManager = User::factory()->create();
        $this->projectManager->assignRole(Role::PROJECT_MANAGER);
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

        $response = $this->postJson('api/projects', $data)
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

        $this->assertDatabaseHas(Project::class, $data);
        $this->assertDatabaseHas(ProjectMember::class, [
            'user_id' => $this->projectManager->id,
            'project_id' => $response->json('id'),
        ]);
    }
}
