<?php

namespace App\Http\Resources;

use App\Http\Resources\User\ClientResource;
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
      'id'            => $this->id,
      'name'          => $this->name,
      'expected_cost' => (float) $this->expected_cost,
      'status'        => $this->status,
      'client'        => new ClientResource($this->whenLoaded('client')),
      'funds'         => ProjectFundResource::collection($this->whenLoaded('projectFunds')),
      'created_at'    => $this->created_at?->toIso8601String(),
    ];
  }
}
