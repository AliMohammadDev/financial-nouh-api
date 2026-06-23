<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
      'job_title'  => $this->job_title,
      'user'       => new UserResource($this->whenLoaded('user')),
      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
