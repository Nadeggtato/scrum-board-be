<?php

namespace Tests\Feature;

use App\Models\Sprint;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;

class SprintControllerTest extends BaseProjectTest
{
    public function test_developers_cant_create_sprint(): void
    {
        Sanctum::actingAs($this->developer);

        $this->postJson(route('sprints.store', [
            'project' => $this->project,
        ]))->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_non_members_cant_create_sprint(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->postJson(route(
            'sprints.store',
            ['project' => $this->project]
        ))->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_invalid_payload_on_create_sprint_endpoint_returns_422(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->postJson(route('sprints.store', [
            'project' => $this->project,
            'name' => str_repeat('a', 260),
            'start' => '2026-05-01',
            'end' => '2026-01-01',
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'end']);
    }

    public function test_create_sprint_endpoint(): void
    {
        Sanctum::actingAs($this->projectManager);
        $data = [
            'name' => 'Sprint 1',
            'start' => '2026-04-10',
            'end' => '2026-04-29',
        ];

        $this->postJson(route('sprints.store', [...$data, 'project' => $this->project]))
            ->assertCreated()
            ->assertJsonStructure([
                'id',
                'name',
                'start',
                'end',
            ]);
        $this->assertDatabaseHas(Sprint::class, [...$data, 'project_id' => $this->project->id]);
    }
}
