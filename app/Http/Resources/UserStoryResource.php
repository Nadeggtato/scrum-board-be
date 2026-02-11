<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'story_points' => $this->story_points,
            'project' => new ProjectResource($this->whenLoaded('project')),
            'sprint' => new SprintResource($this->whenLoaded('sprint')),
        ];
    }
}
