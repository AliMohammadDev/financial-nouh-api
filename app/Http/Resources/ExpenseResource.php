<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
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
          'type'    => 'currency_fund',
          'details' => $this->expenseable,
        ];
      } elseif ($type === 'App\Models\CompanyFundCurrency') {
        $expenseableInfo = [
          'type'    => 'company_fund',
          'details' => $this->expenseable,
        ];
      } elseif ($type === 'App\Models\ProjectFundCurrency') {
        $expenseableInfo = [
          'type'    => 'project_fund',
          'details' => $this->expenseable,
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
      'user'              => new UserResource($this->whenLoaded('user')),
      'created_by'        => new UserResource($this->whenLoaded('creator')),
      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }
}
