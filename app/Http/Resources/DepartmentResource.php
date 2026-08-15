<?php

namespace App\Http\Resources;

use App\Http\Resources\User\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
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
      'name'          => $this->name,
      'main_manager'  => $this->main_manager,

      'employees'    => EmployeeResource::collection($this->whenLoaded('employees')),
      'engineers'    => EmployeeResource::collection($this->whenLoaded('engineers')),
      'projects'     => $this->whenLoaded('projects'),


      'created_at'    => $this->created_at?->format('Y-m-d'),
    ];
  }
}
