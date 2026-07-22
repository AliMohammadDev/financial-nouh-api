<?php

namespace App\Http\Resources;

use App\Http\Resources\User\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectFundResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'          => $this->id,
      'name'        => $this->name,
      'type'        => $this->type,
      'project' => new ProjectResource($this->whenLoaded('project')),
      'currency'    => $this->currency,
      'created_at'  => $this->created_at?->format('Y-m-d'),
    ];
  }
}
