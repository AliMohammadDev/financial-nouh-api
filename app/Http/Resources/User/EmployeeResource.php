<?php

namespace App\Http\Resources\User;

use App\Http\Resources\DepartmentResource;
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
      'status' => $this->status,
      'user'       => new UserResource($this->whenLoaded('user')),
      'department'       => new DepartmentResource($this->whenLoaded('department')),
      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
