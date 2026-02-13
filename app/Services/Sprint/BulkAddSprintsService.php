<?php

namespace App\Services\Sprint;

use App\Models\Project;
use App\Models\Sprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkAddSprintsService
{
    private function buildRows(array $data, Project $project)
    {
        $now = Carbon::now();
        $planStartDate = Carbon::parse(Arr::get($data, 'from'));
        $planEndDate = Carbon::parse(Arr::get($data, 'to'));

        $sprintStartDate = $planStartDate->isWeekend() ?
            $planStartDate->next(Carbon::MONDAY) :
            $planStartDate;
        $sprintDuration = Arr::get($data, 'sprint_duration');

        $namingPattern = Arr::get($data, 'name_pattern');
        $incrementStart = Arr::get($data, 'increment_start');

        $rows = [];

        do {
            $sprintEndDate = $sprintStartDate->copy()->addWeeks($sprintDuration)->previousWeekday();

            $rows[] = [
                'id' => Str::uuid(),
                'name' => $namingPattern === Sprint::PATTERN_INCREMENTAL ?
                    'Sprint '.$incrementStart++ :
                    $sprintStartDate->isoWeekYear().', Week '.$sprintStartDate->isoWeek(),
                'start' => $sprintStartDate,
                'end' => $sprintEndDate,
                'project_id' => $project->id,
                'is_active' => $now->isBetween($sprintStartDate, $sprintEndDate),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $sprintStartDate = $sprintEndDate->copy()->nextWeekday();
        } while ($sprintStartDate->lessThanOrEqualTo($planEndDate));

        return $rows;
    }

    public function execute(array $data, Project $project)
    {
        return DB::transaction(function () use ($data, $project) {
            $rows = $this->buildRows($data, $project);
            $rowCount = count($rows);
            $totalInserted = 0;

            foreach (array_chunk($rows, length: 1000) as $chunk) {
                $totalInserted = Sprint::insertOrIgnore($chunk);
            }

            return [
                'requested' => $rowCount,
                'inserted' => $totalInserted,
                'skipped' => $rowCount - $totalInserted,
            ];
        });

    }
}
