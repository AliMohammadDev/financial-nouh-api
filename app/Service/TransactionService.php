<?php

namespace App\Service;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\ProjectFundCurrency;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class TransactionService
{
  public function __construct(
    private AuditLogService $auditLogService
  ) {}

  // public function findAll(
  //   bool $paginate = false,
  //   int $perPage = 10,
  //   int $page = 1,
  //   array $columns = ["*"]
  // ): LengthAwarePaginator|Collection {

  //   $filters = [
  //     AllowedFilter::callback('search', function ($query, $value) {
  //       $query->where('name', 'like', "%{$value}%");
  //     }),
  //   ];

  //   $query = QueryBuilder::for(Transaction::class)
  //     ->with([
  //       'creator',
  //       'morphFrom',
  //       'morphToFund',
  //     ])
  //     ->allowedFilters(...$filters)
  //     ->defaultSort('-created_at');

  //   $morphRelationsMap = [
  //     CurrencyFund::class => [
  //       'currency',
  //       'fund.user.client',
  //       'fund.user.employee',
  //       'fund.user.admin',
  //       'fund.user.engineer',
  //       'fund.user.craftsmen',
  //       'fund.user.supplier',
  //       'fund.user.trustee',
  //       'fund.user.investor',
  //       'fund.user.dailyWorker',
  //     ],
  //     CompanyFundCurrency::class => [],
  //     ProjectFundCurrency::class => [
  //       'currency',
  //       'projectFund.project',
  //     ],
  //   ];

  //   if ($paginate) {
  //     $paginator = $query->paginate(perPage: $perPage, page: $page, columns: $columns);

  //     $paginator->getCollection()->loadMorph('morphFrom', $morphRelationsMap);
  //     $paginator->getCollection()->loadMorph('morphToFund', $morphRelationsMap);

  //     return $paginator;
  //   }

  //   $transactions = $query->get($columns);

  //   $transactions->loadMorph('morphFrom', $morphRelationsMap);
  //   $transactions->loadMorph('morphToFund', $morphRelationsMap);

  //   return $transactions;
  // }


  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->where('name', 'like', "%{$value}%");
      }),
      AllowedFilter::exact('is_posted'),
      AllowedFilter::exact('created_by'),

      AllowedFilter::callback('date_from', function ($query, $value) {
        $query->whereDate('created_at', '>=', $value);
      }),
      AllowedFilter::callback('date_to', function ($query, $value) {
        $query->whereDate('created_at', '<=', $value);
      }),

      AllowedFilter::callback('fund_id', function ($query, $value) {
        $query->where(function ($q) use ($value) {
          $q->where(function ($sub) use ($value) {
            $sub->where('morph_from_type', CurrencyFund::class)
              ->whereHasMorph('morphFrom', [CurrencyFund::class], function ($sq) use ($value) {
                $sq->where('fund_id', $value);
              });
          })->orWhere(function ($sub) use ($value) {
            $sub->where('morph_to_type', CurrencyFund::class)
              ->whereHasMorph('morphToFund', [CurrencyFund::class], function ($sq) use ($value) {
                $sq->where('fund_id', $value);
              });
          });
        });
      }),

      AllowedFilter::callback('company_fund_id', function ($query, $value) {
        $query->where(function ($q) use ($value) {
          $q->where(function ($sub) use ($value) {
            $sub->where('morph_from_type', CompanyFundCurrency::class)
              ->whereHasMorph('morphFrom', [CompanyFundCurrency::class], function ($sq) use ($value) {
                $sq->where('company_fund_id', $value);
              });
          })->orWhere(function ($sub) use ($value) {
            $sub->where('morph_to_type', CompanyFundCurrency::class)
              ->whereHasMorph('morphToFund', [CompanyFundCurrency::class], function ($sq) use ($value) {
                $sq->where('company_fund_id', $value);
              });
          });
        });
      }),

      AllowedFilter::callback('project_fund_id', function ($query, $value) {
        $query->where(function ($q) use ($value) {
          $q->where(function ($sub) use ($value) {
            $sub->where('morph_from_type', ProjectFundCurrency::class)
              ->whereHasMorph('morphFrom', [ProjectFundCurrency::class], function ($sq) use ($value) {
                $sq->where('project_fund_id', $value);
              });
          })->orWhere(function ($sub) use ($value) {
            $sub->where('morph_to_type', ProjectFundCurrency::class)
              ->whereHasMorph('morphToFund', [ProjectFundCurrency::class], function ($sq) use ($value) {
                $sq->where('project_fund_id', $value);
              });
          });
        });
      }),


      AllowedFilter::callback('currency_fund_id', function ($query, $value) {
        $query->where(function ($q) use ($value) {
          $q->where(function ($sub) use ($value) {
            $sub->where('morph_from_type', CurrencyFund::class)
              ->where('morph_from_id', $value);
          })->orWhere(function ($sub) use ($value) {
            $sub->where('morph_to_type', CurrencyFund::class)
              ->where('morph_to_id', $value);
          });
        });
      }),

      AllowedFilter::callback('company_fund_currency_id', function ($query, $value) {
        $query->where(function ($q) use ($value) {
          $q->where(function ($sub) use ($value) {
            $sub->where('morph_from_type', CompanyFundCurrency::class)
              ->where('morph_from_id', $value);
          })->orWhere(function ($sub) use ($value) {
            $sub->where('morph_to_type', CompanyFundCurrency::class)
              ->where('morph_to_id', $value);
          });
        });
      }),

      AllowedFilter::callback('project_fund_currency_id', function ($query, $value) {
        $query->where(function ($q) use ($value) {
          $q->where(function ($sub) use ($value) {
            $sub->where('morph_from_type', ProjectFundCurrency::class)
              ->where('morph_from_id', $value);
          })->orWhere(function ($sub) use ($value) {
            $sub->where('morph_to_type', ProjectFundCurrency::class)
              ->where('morph_to_id', $value);
          });
        });
      }),
    ];

    $query = QueryBuilder::for(Transaction::class)
      ->select('transactions.*')
      ->with([
        'creator.client',
        'creator.employee',
        'creator.admin',
        'creator.engineer',
        'creator.craftsmen',
        'creator.supplier',
        'creator.trustee',
        'creator.investor',
        'creator.dailyWorker',
        'morphFrom',
        'morphToFund',
      ])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'amount',
        'is_posted',
        'name',
        AllowedSort::callback('creator_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users as c', 'transactions.created_by', '=', 'c.id')
            ->orderBy('c.name', $direction);
        })
      )
      ->defaultSort('-created_at');

    $morphRelationsMap = [
      CurrencyFund::class => [
        'currency',
        'fund.user.client',
        'fund.user.employee',
        'fund.user.admin',
        'fund.user.engineer',
        'fund.user.craftsmen',
        'fund.user.supplier',
        'fund.user.trustee',
        'fund.user.investor',
        'fund.user.dailyWorker',
      ],
      CompanyFundCurrency::class => [],
      ProjectFundCurrency::class => [
        'currency',
        'projectFund.project',
      ],
    ];

    if ($paginate) {
      $paginator = $query->paginate(perPage: $perPage, page: $page, columns: $columns);

      $paginator->getCollection()->loadMorph('morphFrom', $morphRelationsMap);
      $paginator->getCollection()->loadMorph('morphToFund', $morphRelationsMap);

      return $paginator;
    }

    $transactions = $query->get($columns);

    $transactions->loadMorph('morphFrom', $morphRelationsMap);
    $transactions->loadMorph('morphToFund', $morphRelationsMap);

    return $transactions;
  }
  public function findOne(Transaction $transaction): Transaction
  {
    $transaction->load([
      'creator',
      'morphFrom',
      'morphToFund',
    ]);

    $morphRelationsMap = [
      CurrencyFund::class => [
        'currency',
        'fund.user.client',
        'fund.user.employee',
        'fund.user.admin',
        'fund.user.engineer',
        'fund.user.craftsmen',
        'fund.user.supplier',
        'fund.user.trustee',
        'fund.user.investor',
        'fund.user.dailyWorker',
      ],
      CompanyFundCurrency::class => [],
      ProjectFundCurrency::class => [
        'currency',
        'projectFund.project',
      ],
    ];

    $transaction->loadMorph('morphFrom', $morphRelationsMap);
    $transaction->loadMorph('morphToFund', $morphRelationsMap);

    return $transaction;
  }

  public function create(array $data): Transaction
  {
    return DB::transaction(function () use ($data) {
      $data['created_by'] = auth()->id();

      $transaction = Transaction::create($data);
      $transaction->load(['creator', 'morphFrom', 'morphToFund']);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء عملية تحويل/مناقلة جديدة بقيمة: {$transaction->amount}",
        affectedTable: 'transactions'
      );

      return $transaction;
    });
  }

  public function update(Transaction $transaction, array $data): Transaction
  {
    return DB::transaction(function () use ($transaction, $data) {
      $transaction->update($data);
      $transaction->load(['creator', 'morphFrom', 'morphToFund']);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل عملية التحويل (القيمة: {$transaction->amount})",
        affectedTable: 'transactions'
      );

      return $transaction;
    });
  }

  public function delete(Transaction $transaction): bool
  {
    return DB::transaction(function () use ($transaction) {
      $amount = $transaction->amount;
      $deleted = (bool) $transaction->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف عملية التحويل (القيمة السابقة: {$amount})",
          affectedTable: 'transactions'
        );
      }

      return $deleted;
    });
  }
}
