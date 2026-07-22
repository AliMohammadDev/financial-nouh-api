<?php

namespace App\Http\Resources\User;

use App\Http\Resources\CurrencyResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminResource extends JsonResource
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
      'user'       => new UserResource($this->whenLoaded('user')),

      'currencies' => CurrencyResource::collection($this->whenLoaded('currencies')),

      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
