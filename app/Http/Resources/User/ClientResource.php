<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
