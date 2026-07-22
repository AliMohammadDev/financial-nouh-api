<?php

namespace App\Http\Resources\User;

use App\Http\Resources\FundResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
      'name'         => $this->name,
      'email'        => $this->email,
      'phone_number' => $this->phone_number,
      'address'      => $this->address,

      'funds'        => FundResource::collection($this->whenLoaded('funds')),

      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
