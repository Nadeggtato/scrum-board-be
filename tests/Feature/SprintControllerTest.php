<?php

namespace Tests\Feature;

use App\Models\Sprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
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

    public function test_developers_cant_bulk_add_sprints(): void
    {
        Sanctum::actingAs($this->developer);

        $this->postJson(route('sprints.bulk-add', [
            'project' => $this->project,
            'from' => '2026-01-01',
            'to' => '2026-01-25',
        ]))->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_non_members_cant_bulk_add_sprints(): void
    {
        Sanctum::actingAs($this->nonMember);

        $this->postJson(route('sprints.bulk-add', [
            'project' => $this->project,
            'from' => '2026-01-01',
            'to' => '2026-01-25',
        ]))->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_invalid_payload_on_bulk_add_sprints_returns_422(): void
    {
        Sanctum::actingAs($this->projectManager);

        $this->postJson(route('sprints.bulk-add', [
            'project' => $this->project,
            'from' => '2026-01-01',
            'to' => '2025-12-04',
            'name_pattern' => Sprint::PATTERN_INCREMENTAL,
            'sprint_duration' => 6,
        ]))->assertUnprocessable()
            ->assertJsonValidationErrors(['to', 'sprint_duration', 'increment_start']);
    }

    private function bulkAddSprints(array $payload): void
    {
        $this->postJson(
            route('sprints.bulk-add', ['project' => $this->project]),
            $payload
        )->assertCreated();
    }

    private function assertSprintsGenerated(
        Carbon $startDate,
        int $durationWeeks,
        int $expectedCount,
        callable $nameResolver,
        bool $useWeekdayBoundaries = false
    ): void {
        $this->assertDatabaseCount(Sprint::class, $expectedCount);

        $currentStart = $startDate->copy();

        for ($i = 0; $i < $expectedCount; $i++) {
            $endDate = $useWeekdayBoundaries
                ? $currentStart->copy()->addWeeks($durationWeeks)->previousWeekday()
                : $currentStart->copy()->addWeeks($durationWeeks)->subDay();

            $this->assertDatabaseHas('sprints', [
                'name' => $nameResolver($currentStart, $i),
                'start' => $currentStart->format('Y-m-d 00:00:00'),
                'end' => $endDate->format('Y-m-d 00:00:00'),
            ]);

            $currentStart = $useWeekdayBoundaries
                ? $endDate->copy()->nextWeekday()
                : $endDate->copy()->addDay();
        }
    }

    public function test_bulk_add_sprints_with_incremental_name_pattern(): void
    {
        Sanctum::actingAs($this->projectManager);

        $startDate = Carbon::parse('2026-01-01');
        $duration = 2;
        $incrementStart = 2;

        $this->bulkAddSprints([
            'from' => $startDate->format('Y-m-d'),
            'to' => '2026-02-22',
            'name_pattern' => Sprint::PATTERN_INCREMENTAL,
            'increment_start' => $incrementStart,
            'sprint_duration' => $duration,
        ]);

        $this->assertSprintsGenerated(
            startDate: $startDate,
            durationWeeks: $duration,
            expectedCount: 4,
            nameResolver: fn (Carbon $d, int $i) => 'Sprint '.($incrementStart + $i),
        );
    }

    public function test_bulk_add_sprints_with_week_number_name_pattern(): void
    {
        Sanctum::actingAs($this->projectManager);

        $startDate = Carbon::parse('2026-01-01');
        $duration = 2;

        $this->bulkAddSprints([
            'from' => $startDate->format('Y-m-d'),
            'to' => '2026-02-22',
            'name_pattern' => Sprint::PATTERN_WEEK_NUMBER,
            'sprint_duration' => $duration,
        ]);

        $this->assertSprintsGenerated(
            startDate: $startDate,
            durationWeeks: $duration,
            expectedCount: 4,
            nameResolver: fn (Carbon $d) => $d->isoWeekYear().', Week '.$d->isoWeek(),
        );
    }

    public function test_bulk_add_sprints_weekend_start_should_default_to_monday(): void
    {
        Sanctum::actingAs($this->projectManager);

        $startDate = Carbon::parse('2026-02-07');
        $duration = 1;

        $this->bulkAddSprints([
            'from' => $startDate->format('Y-m-d'),
            'to' => '2026-02-22',
            'name_pattern' => Sprint::PATTERN_WEEK_NUMBER,
            'sprint_duration' => $duration,
        ]);

        $adjustedStart = Carbon::parse('2026-02-09');

        $this->assertSprintsGenerated(
            startDate: $adjustedStart,
            durationWeeks: $duration,
            expectedCount: 2,
            nameResolver: fn (Carbon $d) => $d->isoWeekYear().', Week '.$d->isoWeek(),
            useWeekdayBoundaries: true
        );
    }

    public function test_bulk_add_returns_409_when_lock_is_already_acquired(): void
    {
        Sanctum::actingAs($this->projectManager);

        $key = "bulk-add-sprints:{$this->project->id}";
        $lock = Cache::lock($key, 10);
        // dump($lock->get());
        //
        $this->assertTrue($lock->get());

        try {
            $this->postJson(
                route('sprints.bulk-add', ['project' => $this->project]),
                [
                    'from' => '2026-01-01',
                    'to' => '2026-02-22',
                    'name_pattern' => Sprint::PATTERN_WEEK_NUMBER,
                    'sprint_duration' => 2,
                ]
            )
                ->assertStatus(Response::HTTP_CONFLICT)
                ->assertJson([
                    'message' => 'Bulk add already in progress for this project. Try again in a moment.',
                ]);
        } finally {
            optional($lock)->release();
        }
    }
}
