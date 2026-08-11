<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundResource extends JsonResource
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
      'name'       => $this->name,
      'is_locked'   => (bool) $this->is_locked,
      'status'      => $this->status,
      'threshold'   => $this->threshold,
      'description' => $this->description,

      'user'       => $this->whenLoaded('user'),
      'currencies' => CurrencyResource::collection($this->whenLoaded('currencies')),
      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
