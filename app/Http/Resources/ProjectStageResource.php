<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectStageResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'                => $this->id,
      'name'              => $this->name,
      'start_date'        => $this->start_date?->format('Y-m-d'),
      'expected_end_date' => $this->expected_end_date?->format('Y-m-d'),
      'actual_end_date'   => $this->actual_end_date?->format('Y-m-d'),
      'status'            => $this->status,
      'stage_progress'    => $this->stage_progress,
      'project'           => new ProjectResource($this->whenLoaded('project')),
      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }
}
