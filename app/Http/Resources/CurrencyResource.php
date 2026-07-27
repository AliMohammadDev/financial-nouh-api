<?php

namespace App\Http\Resources;

use App\Models\Fund;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    $balance = null;
    $user = null;
    $expenseableType = null;
    $expenseableId = null;

    if ($this->resource->pivot) {
      $balance = $this->pivot->balance;
      $expenseableId = $this->pivot->id; // الـ ID الخاص بجدول الـ Pivot

      if (isset($this->pivot->fund_id) && $this->pivot->getTable() === 'currency_funds') {
        $expenseableType = 'App\\Models\\CurrencyFund';

        $fund = Fund::with('user')->find($this->pivot->fund_id);
        if ($fund && $fund->user) {
          $user = [
            'id'           => $fund->user->id,
            'name'         => $fund->user->name,
            'email'        => $fund->user->email,
            'phone_number' => $fund->user->phone_number,
          ];
        }
      } elseif (isset($this->pivot->project_fund_id) || $this->pivot->getTable() === 'project_fund_currencies') {
        $expenseableType = 'App\\Models\\ProjectFundCurrency';
        $balance = $this->pivot->balance;
      } elseif (isset($this->pivot->company_fund_id) || $this->pivot->getTable() === 'company_fund_currencies') {
        $expenseableType = 'App\\Models\\CompanyFundCurrency';
        $balance = $this->pivot->balance;
      }
    }

    return [
      'id'                => $this->id, // حافظنا على id العملة الأصلي كما هو
      'expenseable_type'  => $expenseableType, // اسم المودل جاهز للإرسال مباشرة
      'expenseable_id'    => $expenseableId,   // الـ ID الخاص بالـ Pivot جاهز للإرسال مباشرة
      'currency'          => $this->currency,
      'symbol'            => $this->symbol,
      'balance'           => $balance,
      'user'              => $user,
      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }
}