<?php

namespace App\Service;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CurrencyService
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
        $query->where('currency', 'like', "%{$value}%")
          ->orWhere('symbol', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Currency::class)
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'currency',
        'symbol'
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

  public function findOne(Currency $currency): Currency
  {
    return $currency;
  }

  public function create(array $data): Currency
  {
    return DB::transaction(function () use ($data) {
      $currency = Currency::create($data);

      $currencyName = $currency->currency ?? 'عملة';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء عملة جديدة: {$currencyName}",
        affectedTable: 'currencies'
      );

      return $currency;
    });
  }

  public function update(Currency $currency, array $data): Currency
  {
    DB::transaction(function () use ($currency, $data) {
      $currency->update($data);

      $currencyName = $currency->currency ?? 'عملة';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات العملة: {$currencyName}",
        affectedTable: 'currencies'
      );
    });

    return $currency;
  }

  public function delete(Currency $currency): bool
  {
    return DB::transaction(function () use ($currency) {
      $currencyName = $currency->currency ?? 'عملة';

      $deleted = (bool) $currency->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف العملة: {$currencyName}",
          affectedTable: 'currencies'
        );
      }

      return $deleted;
    });
  }
}
