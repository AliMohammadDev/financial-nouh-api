<?php

namespace App\Service;

use App\Models\Fund;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class FundService
{
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
    return $fund->load('user');
  }

  public function create(array $data): Fund
  {
    return DB::transaction(function () use ($data) {
      return Fund::create($data);
    });
  }

  public function update(Fund $fund, array $data): Fund
  {
    DB::transaction(function () use ($fund, $data) {
      $fund->update($data);
    });

    return $fund->load('user');
  }

  public function delete(Fund $fund): bool
  {
    return DB::transaction(function () use ($fund) {
      return (bool) $fund->delete();
    });
  }

  public function attachCurrency(Fund $fund, array $data): Fund
  {
    return DB::transaction(function () use ($fund, $data) {
      $fund->currencies()->syncWithoutDetaching([
        $data['currency_id'] => ['balance' => $data['balance']]
      ]);

      return $fund->load(['user', 'currencies']);
    });
  }
}
