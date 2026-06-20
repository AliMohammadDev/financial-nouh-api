<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaterialResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'                 => $this->id,
      'item_id' => $this->item_id,
      'name'               => $this->name,
      'unit'               => $this->unit,
      'description'        => $this->description,
      'structural_item'    => new ItemResource($this->whenLoaded('structuralItem')),
      'created_at'         => $this->created_at?->toIso8601String(),
    ];
  }
}
