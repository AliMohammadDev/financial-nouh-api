<?php

namespace App\Service;

use App\Models\CompanyFund;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CompanyFundService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $query = CompanyFund::with(['currencies'])
      ->latest();

    if ($paginate) {
      return $query->paginate(
        perPage: $perPage,
        page: $page,
        columns: $columns,
      );
    }

    return $query->get($columns);
  }

  public function findOne(int $id): CompanyFund
  {
    return CompanyFund::with(['currencies'])->findOrFail($id);
  }

  public function create(array $data): CompanyFund
  {
    return DB::transaction(function () use ($data) {
      return CompanyFund::create($data);
    });
  }

  public function update(int $id, array $data): CompanyFund
  {
    $fund = CompanyFund::findOrFail($id);

    DB::transaction(function () use ($fund, $data) {
      $fund->update($data);
    });

    return $fund;
  }

  public function delete(int $id): bool
  {
    $fund = CompanyFund::findOrFail($id);
    return DB::transaction(function () use ($fund) {
      return (bool) $fund->delete();
    });
  }

  public function attachCurrency(CompanyFund $companyFund, array $data): CompanyFund
  {
    return DB::transaction(function () use ($companyFund, $data) {
      $companyFund->currencies()->syncWithoutDetaching([
        $data['currency_id'] => ['balance' => $data['balance']]
      ]);

      return $companyFund->load(['currencies']);
    });
  }
}
