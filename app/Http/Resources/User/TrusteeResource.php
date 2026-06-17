<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TrusteeResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'               => $this->id,
      'kinship_relation' => $this->kinship_relation,
      'user'             => new UserResource($this->whenLoaded('user')),
      'created_at'       => $this->created_at?->toIso8601String(),
    ];
  }
}
