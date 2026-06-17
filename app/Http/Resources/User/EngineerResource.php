<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EngineerResource extends JsonResource
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
      'job_title'   => $this->job_title,
      'base_salary' => $this->base_salary,
      'user'        => new UserResource($this->whenLoaded('user')),
      'created_at'  => $this->created_at?->toIso8601String(),
    ];
  }
}
