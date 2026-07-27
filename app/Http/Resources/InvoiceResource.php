<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id'                   => $this->id,
      'invoice_number'       => $this->invoice_number,
      'date'                 => $this->date?->format('Y-m-d'),
      'discount'             => (float) $this->discount,
      'final_total'          => (float) $this->final_total,
      'is_posted'            => (bool) $this->is_posted,
      'is_visible_to_client' => (bool) $this->is_visible_to_client,

      'item'                 => $this->whenLoaded('item', function () {
        return $this->item?->name;
      }),
      'supplier'             => $this->whenLoaded('supplier', function () {
        return $this->supplier?->name;
      }),
      'expense_description'  => $this->whenLoaded('expense', function () {
        return $this->expense?->description;
      }),

      'created_at'           => $this->created_at?->format('Y-m-d'),
    ];
  }
}
