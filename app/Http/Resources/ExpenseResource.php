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
        $fundUser = $this->expenseable->relationLoaded('fund') ? $this->expenseable->fund?->user : null;

        $expenseableInfo = [
          'type'    => 'currency_fund',
          'details' => [
            'id'          => $this->expenseable->id,
            'currency_id' => $this->expenseable->currency_id,
            'fund_id'     => $this->expenseable->fund_id,
            'balance'     => $this->expenseable->balance,
            'currency'    => $this->expenseable->relationLoaded('currency') ? $this->expenseable->currency : null,
            'fund'        => $this->expenseable->relationLoaded('fund') ? $this->expenseable->fund : null,
          ],
          'user_info' => $fundUser ? [
            'user_id'   => $fundUser->id,
            'role_type' => $this->determineRoleType($fundUser),
            'user'      => new UserResource($fundUser),
          ] : null,
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

      'invoices'          => $this->whenLoaded('invoices', function () {
        return $this->invoices->map(function ($invoice) {
          return [
            'id'                   => $invoice->id,
            'invoice_number'       => $invoice->invoice_number,
            'date'                 => $invoice->date?->format('Y-m-d'),
            'discount'             => (float) $invoice->discount,
            'final_total'          => (float) $invoice->final_total,
            'is_posted'            => (bool) $invoice->is_posted,
            'is_visible_to_client' => (bool) $invoice->is_visible_to_client,
            'item'                 => $invoice->relationLoaded('item') ? $invoice->item?->name : null,
            'supplier'             => $invoice->relationLoaded('supplier') ? $invoice->supplier?->name : null,
          ];
        });
      }),

      'user'              => new UserResource($this->whenLoaded('user')),
      'created_by'        => new UserResource($this->whenLoaded('creator')),
      'created_at'        => $this->created_at?->format('Y-m-d'),
    ];
  }

  private function determineRoleType($user): string
  {
    if ($user->relationLoaded('admin') && $user->admin) return 'admin';
    if ($user->relationLoaded('client') && $user->client) return 'client';
    if ($user->relationLoaded('employee') && $user->employee) return 'employee';
    if ($user->relationLoaded('craftsmen') && $user->craftsmen) return 'craftsmen';
    if ($user->relationLoaded('engineer') && $user->engineer) return 'engineer';
    if ($user->relationLoaded('supplier') && $user->supplier) return 'supplier';
    if ($user->relationLoaded('trustee') && $user->trustee) return 'trustee';

    return 'user';
  }
}
