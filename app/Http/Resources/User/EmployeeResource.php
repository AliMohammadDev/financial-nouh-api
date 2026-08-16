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
    $latestPayment = $this->payments->first();
    return [
      'id'         => $this->id,
      'job_title'  => $this->job_title,
      'status' => $this->status,
      'user'       => new UserResource($this->whenLoaded('user')),
      'department'       => new DepartmentResource($this->whenLoaded('department')),


      'last_payment' => $latestPayment ? [
        'id'           => $latestPayment->id,
        'amount'       => (float) $latestPayment->amount,
        'bonuses'      => (float) $latestPayment->bonuses,
        'deductions'   => (float) $latestPayment->deductions,
        'payment_date' => $latestPayment->payment_date?->toDateString(),
      ] : null,

      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
