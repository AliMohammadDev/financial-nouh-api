<?php

namespace App\Service;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\ProjectFundCurrency;
use App\Models\Revenue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RevenueService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $query = Revenue::with(['user', 'receiver', 'revenueable'])
      ->latest();

    if ($paginate) {
      return $query->paginate(perPage: $perPage, page: $page, columns: $columns);
    }

    return $query->get($columns);
  }

  public function findOne(Revenue $revenue): Revenue
  {

    $revenue->load([
      'user.client',
      'user.employee',
      'user.admin',
      'user.engineer',
      'user.craftsmen',
      'user.supplier',
      'user.trustee',
      'receiver',
    ]);

    $revenue->loadMorph('revenueable', [
      CurrencyFund::class => [
        'currency',
        'fund.user.client',
        'fund.user.employee',
        'fund.user.admin',
        'fund.user.engineer',
        'fund.user.craftsmen',
        'fund.user.supplier',
        'fund.user.trustee',
      ],
      CompanyFundCurrency::class => [],
      ProjectFundCurrency::class => [
        'currency',
        'projectFund.project',
      ],
    ]);

    return $revenue;
  }

  public function create(array $data): Revenue
  {
    return DB::transaction(function () use ($data) {
      $revenue = Revenue::create($data);
      return $revenue->load(['user', 'receiver', 'revenueable']);
    });
  }

  public function update(Revenue $revenue, array $data): Revenue
  {
    return DB::transaction(function () use ($revenue, $data) {
      $revenue->update($data);
      return $revenue->load(['user', 'receiver', 'revenueable']);
    });
  }

  public function delete(Revenue $revenue): bool
  {
    return DB::transaction(function () use ($revenue) {
      return (bool) $revenue->delete();
    });
  }
}
