<?php

namespace App\Http\Resources;

use App\Http\Resources\User\ClientResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectFundResource extends JsonResource
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
      'project_id' => $this->project_id,
      'fund'       => $this->relationLoaded('fund') ? [
        'id'      => $this->fund->id,
        'name'    => $this->fund->name,
        'balance' => (float) $this->fund->balance,
      ] : ['id' => $this->fund_id],
      'created_at' => $this->created_at?->toIso8601String(),
    ];
  }
}
