<?php

namespace App\Http\Resources;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class TransactionResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    // === هنا قمنا بإضافة تتبع (Logs) لفحص قيمة العلاقة ونوعها قبل التنسيق ===
    Log::info('Transaction Debug:', [
      'id'                => $this->id,
      'morph_to_type'     => $this->morph_to_type,
      'morph_to_id'       => $this->morph_to_id,
      'relation_loaded'   => $this->relationLoaded('morphToFund'),
      'morph_to_fund_val' => $this->morphToFund ? $this->morphToFund->toArray() : 'NULL_OR_NOT_LOADED',
    ]);

    return [
      'id'              => $this->id,
      'morph_from_type' => $this->morph_from_type,
      'morph_from_id'   => $this->morph_from_id,
      'morph_from_info' => $this->formatMorphRelation($this->morphFrom, $this->morph_from_type),
      'morph_to_type'   => $this->morph_to_type,
      'morph_to_id'     => $this->morph_to_id,
      'morph_to_info'   => $this->formatMorphRelation($this->morphToFund ?? $this->morphTo, $this->morph_to_type),
      'name'            => $this->name,
      'amount'          => (float) $this->amount,
      'user'            => new UserResource($this->whenLoaded('user')),
      'created_by'      => new UserResource($this->whenLoaded('creator')),
      'created_at'      => $this->created_at?->format('Y-m-d'),
    ];
  }

  private function formatMorphRelation($relationModel, ?string $type): ?array
  {
    if (!$relationModel) {
      return null;
    }

    if ($type === 'App\Models\CurrencyFund') {
      $fundUser = $relationModel->relationLoaded('fund') ? $relationModel->fund?->user : null;

      return [
        'type'    => 'currency_fund',
        'details' => [
          'id'          => $relationModel->id,
          'currency_id' => $relationModel->currency_id,
          'fund_id'     => $relationModel->fund_id,
          'balance'     => $relationModel->balance,
          'currency'    => $relationModel->relationLoaded('currency') ? $relationModel->currency : null,
          'fund'        => $relationModel->relationLoaded('fund') ? $relationModel->fund : null,
        ],
        'user_info' => $fundUser ? [
          'user_id'   => $fundUser->id,
          'role_type' => $this->determineRoleType($fundUser),
          'user'      => new UserResource($fundUser),
        ] : null,
      ];
    }

    if ($type === 'App\Models\CompanyFundCurrency') {
      return [
        'type'    => 'company_fund',
        'details' => $relationModel,
      ];
    }

    if ($type === 'App\Models\ProjectFundCurrency') {
      return [
        'type'    => 'project_fund',
        'details' => $relationModel,
      ];
    }

    return [
      'type'    => 'unknown',
      'details' => $relationModel,
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
    if ($user->relationLoaded('investor') && $user->investor) return 'investor';
    if ($user->relationLoaded('dailyWorker') && $user->dailyWorker) return 'daily_worker';

    return 'user';
  }
}
