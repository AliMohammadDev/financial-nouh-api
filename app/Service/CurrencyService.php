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
      return Currency::create($data);
    });
  }

  public function update(Currency $currency, array $data): Currency
  {
    DB::transaction(function () use ($currency, $data) {
      $currency->update($data);
    });

    return $currency;
  }

  public function delete(Currency $currency): bool
  {
    return DB::transaction(function () use ($currency) {
      return (bool) $currency->delete();
    });
  }
}
