<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    $expenseableInfo = null;

    if ($this->relationLoaded('expenseable') && $this->expenseable) {
      $type = $this->expenseable_type;

      if ($type === 'App\Models\CurrencyFund') {
        $expenseableInfo = [
          'type'    => 'user',
          'user_id' => $this->expenseable->fund?->user_id ?? null,
          'fund_id' => $this->expenseable->fund_id ?? null,
          'id'      => $this->expenseable->id,
        ];
      } elseif ($type === 'App\Models\CompanyFundCurrency') {
        $expenseableInfo = [
          'type'            => 'company',
          'company_fund_id' => $this->expenseable->company_fund_id ?? null,
          'id'              => $this->expenseable->id,
        ];
      } elseif ($type === 'App\Models\ProjectFundCurrency') {
        $expenseableInfo = [
          'type'            => 'project',
          'project_fund_id' => $this->expenseable->project_fund_id ?? null,
          'id'              => $this->expenseable->id,
        ];
      }
    }

    return [
      'id'                => $this->id,
      'expenseable_type'  => $this->expenseable_type,
      'expenseable_id'    => $this->expenseable_id,
      'expenseable_info'  => $expenseableInfo,
      'description'       => $this->description,
      'amount'            => (float) $this->amount,
      'is_posted'         => (bool) $this->is_posted,

      'user'              => $this->whenLoaded('user', function () {
        return $this->user?->name;
      }),

      'created_by'        => $this->whenLoaded('creator', function () {
        return $this->creator?->name;
      }),

      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }
}
