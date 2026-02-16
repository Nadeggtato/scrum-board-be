<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'name' => $this->name,
            'created_by' => new UserResource($this->whenLoaded('creator')),
            'sprints' => SprintResource::collection($this->whenLoaded('sprints')),
            'members' => UserResource::collection($this->whenLoaded('members')),
        ];
    }
}
