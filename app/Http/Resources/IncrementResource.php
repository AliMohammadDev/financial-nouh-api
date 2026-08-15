<?php

namespace App\Http\Resources;

use App\Http\Resources\User\EmployeeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IncrementResource extends JsonResource
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
      'date'        => $this->date?->format('Y-m-d'),
      'amount'      => (float) $this->amount,
      'reason'      => $this->reason,
      'employee'    => new EmployeeResource($this->whenLoaded('employee')),
      'created_at'  => $this->created_at?->format('Y-m-d'),
    ];
  }
}
