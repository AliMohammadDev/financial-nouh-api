<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTeamResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'         => $this->id,
      'name'       => $this->name,
      'user_id'    => $this->user_id,
      'project_id' => $this->project_id,
      'user'       => new UserResource($this->whenLoaded('user')),
      'project'    => new ProjectResource($this->whenLoaded('project')),
      'created_at' => $this->created_at?->format('Y-m-d'),

    ];
  }
}
