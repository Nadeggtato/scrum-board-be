<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectMember;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class ProjectControllerTest extends BaseProjectTest
{
    public function test_developers_cant_create_project(): void
    {
        Sanctum::actingAs($this->developer);

        $this->postJson(route('projects.store'), [
            'name' => fake()->word(),
        ])->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_invalid_payload_on_create_project_endpoint(): void
    {
        Sanctum::actingAs($this->projectManager);
        $data = [
            'name' => str_repeat('a', 256),
        ];

        $this->postJson(route('projects.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }

    public function test_create_project(): void
    {
        Sanctum::actingAs($this->projectManager);
        $data = [
            'name' => fake()->word(),
        ];

        $response = $this->postJson(route('projects.store'), $data)
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

        $this->patchJson(
            route('projects.update', ['project' => $this->project]),
            ['name' => fake()->word()]
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_non_member_cant_update_project(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->patchJson(
            route('projects.update', ['project' => $this->project]),
            ['name' => fake()->word()]
        )->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_invalid_payload_on_update_project_endpoint(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->patchJson(
            route('projects.update', ['project' => $this->project]),
            []
        )->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'is_active']);
    }

    public function test_update_project()
    {
        Sanctum::actingAs($this->projectManager);
        $newName = fake()->word();

        $response = $this->patchJson(
            route('projects.update', ['project' => $this->project]),
            [
                'name' => $newName,
                'is_active' => false,
            ])->assertOk()
            ->assertJsonPath('name', $newName);

        $this->assertDatabaseHas(Project::class, [
            'id' => $response->json('id'),
            'name' => $newName,
            'is_active' => false,
        ]);
    }

    public function test_non_member_cant_view_project(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->getJson(route('projects.show', ['project' => $this->project]))
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_view_project_by_id(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->getJson(
            route('projects.show', [
                'project' => $this->project,
                'include' => 'creator,sprints,members',
            ]))->assertOk()
            ->assertJsonStructure([
                'id',
                'name',
                'created_by',
                'sprints',
                'members',
            ]);
    }
}
