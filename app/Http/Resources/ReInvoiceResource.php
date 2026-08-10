<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReInvoiceResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    $reinvoiceableInfo = null;

    if ($this->relationLoaded('reinvoiceable') && $this->reinvoiceable) {
      $type = $this->reinvoiceable_type;

      if ($type === \App\Models\CurrencyFund::class) {
        $fundUser = $this->reinvoiceable->relationLoaded('fund') ? $this->reinvoiceable->fund?->user : null;

        $reinvoiceableInfo = [
          'type'    => 'currency_fund',
          'details' => [
            'id'          => $this->reinvoiceable->id,
            'currency_id' => $this->reinvoiceable->currency_id,
            'fund_id'     => $this->reinvoiceable->fund_id,
            'balance'     => (float) $this->reinvoiceable->balance,
            'currency'    => $this->reinvoiceable->relationLoaded('currency') ? $this->reinvoiceable->currency : null,
            'fund'        => $this->reinvoiceable->relationLoaded('fund') ? $this->reinvoiceable->fund : null,
          ],
          'user_info' => $fundUser ? [
            'user_id'   => $fundUser->id,
            'role_type' => $this->determineRoleType($fundUser),
            'user'      => new UserResource($fundUser),
          ] : null,
        ];
      } elseif ($type === \App\Models\CompanyFundCurrency::class) {
        $reinvoiceableInfo = [
          'type'    => 'company_fund',
          'details' => $this->reinvoiceable,
        ];
      } elseif ($type === \App\Models\ProjectFundCurrency::class) {
        $projectFund = $this->reinvoiceable;
        $project = $projectFund->relationLoaded('projectFund') ? $projectFund->projectFund?->project : null;

        $reinvoiceableInfo = [
          'type'    => 'project_fund',
          'details' => [
            'id'              => $projectFund->id,
            'project_fund_id' => $projectFund->project_fund_id,
            'currency_id'     => $projectFund->currency_id,
            'balance'         => (float) $projectFund->balance,
            'currency'        => $projectFund->relationLoaded('currency') ? $projectFund->currency : null,
          ],
          'project_info' => $project ? [
            'project_id'   => $project->id,
            'name'         => $project->name ?? null,
            'project'      => $project,
          ] : null,
        ];
      }
    }

    return [
      'id'                   => $this->id,
      'reinvoice_number'     => $this->reinvoice_number,
      'reinvoiceable_type'   => $this->reinvoiceable_type,
      'reinvoiceable_id'     => $this->reinvoiceable_id,
      'reinvoiceable_info'   => $reinvoiceableInfo,
      'date'                 => $this->date?->format('Y-m-d'),
      'discount'             => (float) $this->discount,
      'final_total'          => (float) $this->final_total,
      'is_posted'            => (bool) $this->is_posted,
      'is_visible_to_client' => (bool) $this->is_visible_to_client,
      'item'                 => $this->whenLoaded('item', function () {
        return $this->item?->name;
      }),
      'supplier' => $this->whenLoaded('supplier', function () {
        return [
          'id'   => $this->supplier->id,
          'name' => $this->supplier->user?->name,
          'email' => $this->supplier->user?->email,
        ];
      }),
      'created_at'           => $this->created_at?->format('Y-m-d'),
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
