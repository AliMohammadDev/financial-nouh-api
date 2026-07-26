<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'                => $this->id,
      'expenseable_type'  => $this->expenseable_type,
      'expenseable_id'    => $this->expenseable_id,
      'description'       => $this->description,
      'amount'            => (float) $this->amount,
      'is_posted'         => (bool) $this->is_posted,

      'user'              => $this->whenLoaded('user', function () {
        return $this->user?->name;
      }),

      'created_by'        => $this->whenLoaded('creator', function () {
        return $this->creator?->name;
      }),

      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }
}
