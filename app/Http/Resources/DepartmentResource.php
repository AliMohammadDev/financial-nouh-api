<?php

namespace App\Http\Resources;

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
      'department_id' => $this->Department_ID,
      'name'          => $this->Name,
      'main_manager'  => $this->Main_Manager,
      'created_at'    => $this->created_at?->format('Y-m-d'),
    ];
  }
}
