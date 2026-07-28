<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceItemResource extends JsonResource
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
      'invoice'          => new InvoiceResource($this->whenLoaded('invoice')),
      'material'         => new MaterialResource($this->whenLoaded('material')),
      'item_description' => $this->item_description,
      'unit'             => $this->unit,
      'quantity'         => (float) $this->quantity,
      'unit_price'       => (float) $this->unit_price,
      'total_price'      => (float) $this->total_price,
      'created_at'       => $this->created_at?->format('Y-m-d'),
    ];
  }
}
