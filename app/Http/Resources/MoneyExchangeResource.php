<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoneyExchangeResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    $exchangeableInfo = null;

    if ($this->relationLoaded('exchangeable') && $this->exchangeable) {
      $type = $this->exchangeable_type;

      if ($type === 'App\Models\CurrencyFund') {
        $fundUser = $this->exchangeable->relationLoaded('fund') ? $this->exchangeable->fund?->user : null;

        $exchangeableInfo = [
          'type'    => 'currency_fund',
          'details' => [
            'id'          => $this->exchangeable->id,
            'currency_id' => $this->exchangeable->currency_id,
            'fund_id'     => $this->exchangeable->fund_id,
            'balance'     => $this->exchangeable->balance,
            'currency'    => $this->exchangeable->relationLoaded('currency') ? $this->exchangeable->currency : null,
            'fund'        => $this->exchangeable->relationLoaded('fund') ? $this->exchangeable->fund : null,
          ],
          'user_info' => $fundUser ? [
            'user_id'   => $fundUser->id,
            'role_type' => $this->determineRoleType($fundUser),
            'user'      => new UserResource($fundUser),
          ] : null,
        ];
      } elseif ($type === 'App\Models\CompanyFundCurrency') {
        $exchangeableInfo = [
          'type'    => 'company_fund',
          'details' => $this->exchangeable,
        ];
      } elseif ($type === 'App\Models\ProjectFundCurrency') {
        $exchangeableInfo = [
          'type'    => 'project_fund',
          'details' => $this->exchangeable,
        ];
      }
    }

    return [
      'id'                => $this->id,
      'exchangeable_type' => $this->exchangeable_type,
      'exchangeable_id'   => $this->exchangeable_id,
      'exchangeable_info' => $exchangeableInfo,
      'from_currency'     => new CurrencyResource($this->whenLoaded('fromCurrency')),
      'to_currency'       => new CurrencyResource($this->whenLoaded('toCurrency')),
      'amount'            => $this->amount,
      'exchange_rate'     => (float) $this->exchange_rate,
      'operation'         => $this->operation,
      'converted_amount' => (float) $this->converted_amount,
      'exp'               => $this->exp,
      'created_by'        => new UserResource($this->whenLoaded('creator')),
      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }

  private function determineRoleType($user): string
  {
    $roles = ['admin', 'client', 'employee', 'craftsmen', 'engineer', 'supplier', 'trustee'];

    foreach ($roles as $role) {
      if ($user->relationLoaded($role) && $user->{$role}) {
        return $role;
      }
    }

    return 'user';
  }
}
