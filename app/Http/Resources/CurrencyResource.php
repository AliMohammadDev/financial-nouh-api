<?php

namespace App\Http\Resources;

use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    $fund = $this->whenPivotLoaded('currency_funds', function () {
      return Fund::with('user')->find($this->pivot->fund_id);
    });
    return [
      'id'         => $this->id,
      'currency'   => $this->currency,
      'symbol'     => $this->symbol,
      'balance'    => $this->whenPivotLoaded('currency_funds', function () {
        return $this->pivot->balance;
      }),
      'user'       => $fund && $fund->relationLoaded('user') ? [
        'id'           => $fund->user->id,
        'name'         => $fund->user->name,
        'email'        => $fund->user->email,
        'phone_number' => $fund->user->phone_number,
      ] : null,
      'created_at' => $this->created_at?->format('Y-m-d'),
    ];
  }
}
