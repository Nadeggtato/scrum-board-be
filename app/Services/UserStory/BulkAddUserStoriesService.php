<?php

namespace App\Services\UserStory;

use App\Models\Project;
use App\Models\UserStory;
use Arr;
use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class BulkAddUserStoriesService
{
    private function buildRows(Project $project, array $data)
    {
        $now = Carbon::now();
        $projectId = $project->id;
        $userStories = Str::of(Arr::get($data, 'user_stories', ''))
            ->explode(PHP_EOL);
        $valid = [];
        $invalid = [];

        foreach ($userStories as $story) {
            if (Str::length($story) <= 40) {
                $valid[] = [
                    'description' => $story,
                    'id' => Str::uuid(),
                    'project_id' => $projectId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];

                continue;
            }

            $invalid[] = $story;
        }

        return [
            'valid' => $valid,
            'invalid' => $invalid,
        ];
    }

    public function execute(Project $project, array $data)
    {
        return DB::transaction(function () use ($project, $data) {
            $result = $this->buildRows($project, $data);
            $validUserStories = Arr::get($result, 'valid');
            $invalidUserStories = Arr::get($result, 'invalid');
            $totalSaved = 0;

            foreach (array_chunk($validUserStories, 1000) as $userStory) {
                $totalSaved = UserStory::insertOrIgnore($userStory);
            }

            $validUserStoriesCount = count($validUserStories);
            $invalidUserStoriesCount = count($invalidUserStories);
            $totalCount = $validUserStoriesCount + $invalidUserStoriesCount;

            return [
                'requested_count' => $totalCount,
                'invalid' => $invalidUserStories,
                'saved' => $totalSaved,
                'skipped' => $totalCount - $totalSaved - count($invalidUserStories),
            ];
        });
    }
}
