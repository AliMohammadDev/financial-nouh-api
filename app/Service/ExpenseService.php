<?php

namespace App\Service;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\Expense;
use App\Models\ProjectFundCurrency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ExpenseService
{


  //   public function findAll(
  //   bool $paginate = false,
  //   int $perPage = 10,
  //   int $page = 1,
  //   array $columns = ["*"]
  // ): LengthAwarePaginator|Collection {

  //   $query = Expense::with([
  //     'user.client',
  //     'user.employee',
  //     'user.admin',
  //     'user.engineer',
  //     'user.craftsmen',
  //     'user.supplier',
  //     'user.trustee',
  //     'creator',
  //     'expenseable'
  //   ])->latest();

  //   if ($paginate) {
  //     $paginator = $query->paginate(perPage: $perPage, page: $page, columns: $columns);

  //     $paginator->getCollection()->loadMorph('expenseable', [
  //       CurrencyFund::class => [
  //         'currency',
  //         'fund.user.client',
  //         'fund.user.employee',
  //         'fund.user.admin',
  //         'fund.user.engineer',
  //         'fund.user.craftsmen',
  //         'fund.user.supplier',
  //         'fund.user.trustee',
  //       ],
  //       CompanyFundCurrency::class => [],
  //       ProjectFundCurrency::class => [
  //         'currency',
  //         'projectFund.project',
  //       ],
  //     ]);

  //     return $paginator;
  //   }

  //   $expenses = $query->get($columns);

  //   $expenses->loadMorph('expenseable', [
  //     CurrencyFund::class => [
  //       'currency',
  //       'fund.user.client',
  //       'fund.user.employee',
  //       'fund.user.admin',
  //       'fund.user.engineer',
  //       'fund.user.craftsmen',
  //       'fund.user.supplier',
  //       'fund.user.trustee',
  //     ],
  //     CompanyFundCurrency::class => [],
  //     ProjectFundCurrency::class => [
  //       'currency',
  //       'projectFund.project',
  //     ],
  //   ]);

  //   return $expenses;
  // }
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $query = Expense::with([
      'user.client',
      'user.employee',
      'user.admin',
      'user.engineer',
      'user.craftsmen',
      'user.supplier',
      'user.trustee',
      'creator',
      'expenseable',
    ])
      ->latest();

    if ($paginate) {
      return $query->paginate(perPage: $perPage, page: $page, columns: $columns);
    }

    return $query->get($columns);
  }

  public function findOne(Expense $expense): Expense
  {
    $expense->load([
      'user.client',
      'user.employee',
      'user.admin',
      'user.engineer',
      'user.craftsmen',
      'user.supplier',
      'user.trustee',
      'creator',
    ]);

    $expense->loadMorph('expenseable', [
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

    return $expense;
  }
  public function create(array $data): Expense
  {
    return DB::transaction(function () use ($data) {
      $expense = Expense::create($data);
      return $expense->load(['user', 'creator', 'expenseable']);
    });
  }

  public function update(Expense $expense, array $data): Expense
  {
    return DB::transaction(function () use ($expense, $data) {
      $expense->update($data);
      return $expense->load(['user', 'creator', 'expenseable']);
    });
  }

  public function delete(Expense $expense): bool
  {
    return DB::transaction(function () use ($expense) {
      return (bool) $expense->delete();
    });
  }
}
