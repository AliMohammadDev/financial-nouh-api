<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    $revenueableInfo = null;

    if ($this->relationLoaded('revenueable') && $this->revenueable) {
      $type = $this->revenueable_type;

      if ($type === 'App\Models\CurrencyFund') {
        $revenueableInfo = [
          'type'    => 'user',
          'user_id' => $this->revenueable->fund?->user_id ?? null,
          'fund_id' => $this->revenueable->fund_id ?? null,
          'id'      => $this->revenueable->id,
        ];
      } elseif ($type === 'App\Models\CompanyFundCurrency') {
        $revenueableInfo = [
          'type'            => 'company',
          'company_fund_id' => $this->revenueable->company_fund_id ?? null,
          'id'              => $this->revenueable->id,
        ];
      } elseif ($type === 'App\Models\ProjectFundCurrency') {
        $revenueableInfo = [
          'type'            => 'project',
          'project_fund_id' => $this->revenueable->project_fund_id ?? null,
          'id'              => $this->revenueable->id,
        ];
      }
    }

    return [
      'id'                => $this->id,
      'revenueable_type'  => $this->revenueable_type,
      'revenueable_id'    => $this->revenueable_id,
      'revenueable_info'  => $revenueableInfo,
      'statement'         => $this->statement,
      'amount'            => (float) $this->amount,
      'is_posted'         => (bool) $this->is_posted,

      'user'              => $this->whenLoaded('user', function () {
        return $this->user?->name;
      }),

      'received_by'       => $this->whenLoaded('receiver', function () {
        return $this->receiver?->name;
      }),

      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }
}
