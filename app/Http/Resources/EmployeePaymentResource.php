<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePaymentResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'           => $this->id,
      'employee_id'  => $this->employee_id,
      'employee'     => $this->relationLoaded('employee') ? $this->employee : null,
      'bonuses'      => (float) $this->bonuses,
      'deductions'   => (float) $this->deductions,
      'payment_date' => $this->payment_date?->toDateString(),
      'amount'       => (float) $this->amount,
      'created_at' => $this->created_at?->format('Y-m-d'),

    ];
  }
}
