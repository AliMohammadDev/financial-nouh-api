<?php

namespace App\Http\Resources;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\ProjectFundCurrency;
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

      'item'                 => new ItemResource($this->whenLoaded('item')),
      'supplier'             => $this->whenLoaded('supplier', function () {
        return $this->supplier?->name;
      }),

      'expense'              => $this->whenLoaded('expense', function () {
        return [
          'id'               => $this->expense->id,
          'description'      => $this->expense->description,
          'amount'           => (float) $this->expense->amount,
          'is_posted'        => (bool) $this->expense->is_posted,
          'expenseable_type' => $this->expense->expenseable_type,
          'expenseable_id'   => $this->expense->expenseable_id,

          'expenseable'      => $this->expense->relationLoaded('expenseable') && $this->expense->expenseable ? match (get_class($this->expense->expenseable)) {

            CurrencyFund::class => [
              'type'     => 'currency_fund',
              'pivot_id' => $this->expense->expenseable->id,
              'balance'  => (float) $this->expense->expenseable->balance,
              'currency' => [
                'id'   => $this->expense->expenseable->currency?->id,
                'name' => $this->expense->expenseable->currency?->name,
              ],
              'fund'     => [
                'id'      => $this->expense->expenseable->fund?->id,
                'name'    => $this->expense->expenseable->fund?->name,
                'user_id' => $this->expense->expenseable->fund?->user_id,
              ],
            ],

            ProjectFundCurrency::class => [
              'type'         => 'project_fund_currency',
              'pivot_id'     => $this->expense->expenseable->id,
              'balance'      => (float) $this->expense->expenseable->balance,
              'currency'     => [
                'id'   => $this->expense->expenseable->currency?->id,
                'name' => $this->expense->expenseable->currency?->name,
              ],
              'project_fund' => [
                'id'         => $this->expense->expenseable->projectFund?->id,
                'name'       => $this->expense->expenseable->projectFund?->name,
                'project_id' => $this->expense->expenseable->projectFund?->project_id,
              ],
            ],

            CompanyFundCurrency::class => [
              'type'         => 'company_fund_currency',
              'pivot_id'     => $this->expense->expenseable->id,
              'balance'      => (float) $this->expense->expenseable->balance,
              'currency'     => [
                'id'   => $this->expense->expenseable->currency?->id,
                'name' => $this->expense->expenseable->currency?->name,
              ],
              'company_fund' => [
                'id'   => $this->expense->expenseable->companyFund?->id,
                'name' => $this->expense->expenseable->companyFund?->name,
              ],
            ],

            default => $this->expense->expenseable,
          } : null,
        ];
      }),

      'created_at'           => $this->created_at?->format('Y-m-d'),
    ];
  }
}
