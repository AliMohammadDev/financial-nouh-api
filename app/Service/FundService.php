<?php

namespace App\Service;

use App\Models\Fund;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class FundService
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
        $query->where('name', 'like', "%{$value}%")
          ->orWhereHas('user', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
          });
      }),
    ];

    $query = QueryBuilder::for(Fund::class)
      ->with(['user', 'currencies'])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'name',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'funds.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction);
        })
      )
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

  public function findOne(Fund $fund): Fund
  {
    return $fund->load(['currencies', 'user']);
  }

  public function create(array $data): Fund
  {
    return DB::transaction(function () use ($data) {
      $fund = Fund::create($data);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء صندوق جديد: {$fund->name}",
        affectedTable: 'funds'
      );

      return $fund;
    });
  }

  public function update(Fund $fund, array $data): Fund
  {
    DB::transaction(function () use ($fund, $data) {
      $fund->update($data);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الصندوق: {$fund->name}",
        affectedTable: 'funds'
      );
    });

    return $fund->load('user');
  }

  public function delete(Fund $fund): bool
  {

    if ($fund->currencies()->exists()) {
      abort(422, 'لا يمكن حذف الصندوق لأنه مرتبط بعلمات مالية. يجب عليك فك ارتباط العملات  أولاً قبل محاولة حذف الصندوق.');
    }
    return DB::transaction(function () use ($fund) {
      $fundName = $fund->name ?? 'صندوق';

      $deleted = (bool) $fund->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الصندوق: {$fundName}",
          affectedTable: 'funds'
        );
      }

      return $deleted;
    });
  }

  public function attachCurrency(Fund $fund, array $data): Fund
  {
    return DB::transaction(function () use ($fund, $data) {
      $fund->currencies()->syncWithoutDetaching([
        $data['currency_id'] => ['balance' => $data['balance']]
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بربط عملة جديدة (برصيد: {$data['balance']}) بالصندوق: {$fund->name}",
        affectedTable: 'fund_currencies'
      );

      return $fund->load(['user', 'currencies']);
    });
  }

  public function detachCurrency(Fund $fund, array $data): Fund
  {
    return DB::transaction(function () use ($fund, $data) {
      $fund->currencies()->detach($data['currency_id']);

      $this->auditLogService->log(
        actionType: 'حذف',
        description: "قام بفك ارتباط العملة من الصندوق: {$fund->name}",
        affectedTable: 'fund_currencies'
      );

      return $fund->load(['user', 'currencies']);
    });
  }
}
