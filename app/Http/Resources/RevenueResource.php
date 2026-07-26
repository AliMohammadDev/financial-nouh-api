<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueResource extends JsonResource
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
      'revenueable_type'  => $this->revenueable_type,
      'revenueable_id'    => $this->revenueable_id,
      'statement'         => $this->statement,
      'amount'            => (float) $this->amount,
      'is_posted'         => (bool) $this->is_posted,

      'user'              => $this->whenLoaded('user', function () {
        return $this->user?->name;
      }),

      'received_by'       => $this->whenLoaded('receiver', function () {
        return $this->receiver?->name;
      }),

      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }
}
