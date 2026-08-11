<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevenueResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    $revenueableInfo = null;

    if ($this->relationLoaded('revenueable') && $this->revenueable) {
      $type = $this->revenueable_type;

      if ($type === \App\Models\CurrencyFund::class) {
        $fundUser = $this->revenueable->relationLoaded('fund') ? $this->revenueable->fund?->user : null;

        $revenueableInfo = [
          'type'    => 'currency_fund',
          'details' => [
            'id'          => $this->revenueable->id,
            'currency_id' => $this->revenueable->currency_id,
            'fund_id'     => $this->revenueable->fund_id,
            'balance'     => $this->revenueable->balance,
            'currency'    => $this->revenueable->relationLoaded('currency') ? $this->revenueable->currency : null,
            'fund'        => $this->revenueable->relationLoaded('fund') ? $this->revenueable->fund : null,
          ],
          'user_info' => $fundUser ? [
            'user_id'   => $fundUser->id,
            'role_type' => $this->determineRoleType($fundUser),
            'user'      => new UserResource($fundUser),
          ] : null,
        ];
      } elseif ($type === \App\Models\CompanyFundCurrency::class) {
        $revenueableInfo = [
          'type'    => 'company_fund',
          'details' => $this->revenueable,
        ];
      } elseif ($type === \App\Models\ProjectFundCurrency::class) {
        $projectFund = $this->revenueable;
        $project = $projectFund->relationLoaded('projectFund') ? $projectFund->projectFund?->project : null;

        $revenueableInfo = [
          'type'    => 'project_fund',
          'details' => [
            'id'              => $projectFund->id,
            'project_fund_id' => $projectFund->project_fund_id,
            'currency_id'     => $projectFund->currency_id,
            'balance'         => $projectFund->balance,
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
      'id'                => $this->id,
      'revenueable_type'  => $this->revenueable_type,
      'revenueable_id'    => $this->revenueable_id,
      'revenueable_info'  => $revenueableInfo,
      'statement'         => $this->statement,
      'amount'            => (float) $this->amount,
      'note'              => $this->note,
      'received_by'       => new UserResource($this->whenLoaded('receiver')),
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
