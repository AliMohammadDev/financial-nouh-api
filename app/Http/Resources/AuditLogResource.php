<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditLogResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'             => $this->id,
      'action_type'    => $this->action_type,
      'affected_table' => $this->affected_table,
      'description'    => $this->description,
      'user'           => $this->whenLoaded('user', function () {
        return [
          'id'    => $this->user->id,
          'name'  => $this->user->name,
          'email' => $this->user->email,
        ];
      }),
      'created_at'     => $this->created_at?->format('Y-m-d H:i:s'),
    ];
  }
}
