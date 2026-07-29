<?php

namespace App\Service\User;

use App\Models\Investor;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InvestorService
{

  public function __construct(
    private AuditLogService $auditLogService
  ) {}


  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->whereHas('user', function ($q) use ($value) {
          $q->where('name', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone_number', 'like', "%{$value}%");
        });
      }),
    ];

    $query = QueryBuilder::for(Investor::class)
      ->with([
        'user.funds.currencies',
      ])
      ->allowedFilters(...$filters)
      ->defaultSort('-created_at');

    if ($paginate) {
      return $query->paginate(
        perPage: $perPage,
        page: $page,
        columns: $columns,
      );
    }

    return $query->get($columns);
  }

  public function findOne(Investor $investor): Investor
  {
    return $investor->load('user.funds.currencies');
  }

  public function create(array $data): Investor
  {
    return DB::transaction(function () use ($data) {
      $data['password'] = Hash::make($data['password']);
      $user = User::create($data);

      $investor = Investor::create([
        'user_id'          => $user->id,
        'investment_ratio' => $data['investment_ratio'] ?? null
      ]);
      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل مستثمر جديد: {$user->name} (نسبة الاستثمار: {$investor->investment_ratio}%)",
        affectedTable: 'investors'
      );

      return $investor->load('user');
    });
  }

  public function update(Investor $investor, array $data): Investor
  {
    DB::transaction(function () use ($investor, $data) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $investor->user()->update(array_diff_key($data, ['investment_ratio' => 1]));

      if (isset($data['investment_ratio'])) {
        $investor->update(['investment_ratio' => $data['investment_ratio']]);
      }

      $investor->load('user');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المستثمر: {$investor->user->name}",
        affectedTable: 'investors'
      );
    });

    return $investor->load('user');
  }

  public function delete(Investor $investor): bool
  {
    return DB::transaction(function () use ($investor) {
      $investorName = $investor->user?->name ?? 'غير معروف';

      $deleted = (bool) $investor->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المستثمر: {$investorName}",
          affectedTable: 'investors'
        );
      }

      return $deleted;
    });
  }
}
