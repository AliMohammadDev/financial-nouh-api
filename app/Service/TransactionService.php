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
use Spatie\QueryBuilder\QueryBuilder;

class TransactionService
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
        $query->where('name', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Transaction::class)
      ->with([
        'user.client',
        'user.employee',
        'user.admin',
        'user.engineer',
        'user.craftsmen',
        'user.supplier',
        'user.trustee',
        'user.investor',
        'user.dailyWorker',
        'creator',
        'morphFrom',
        'morphToFund', // تأكدنا من استخدام اسم العلاقة الصحيح هنا
      ])
      ->allowedFilters(...$filters)
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
      $paginator->getCollection()->loadMorph('morphToFund', $morphRelationsMap); // تم التصحيح هنا أيضاً

      return $paginator;
    }

    $transactions = $query->get($columns);

    $transactions->loadMorph('morphFrom', $morphRelationsMap);
    $transactions->loadMorph('morphToFund', $morphRelationsMap); // تم التصحيح هنا أيضاً

    return $transactions;
  }

  public function findOne(Transaction $transaction): Transaction
  {
    $transaction->load([
      'user.client',
      'user.employee',
      'user.admin',
      'user.engineer',
      'user.craftsmen',
      'user.supplier',
      'user.trustee',
      'user.investor',
      'user.dailyWorker',
      'creator',
      'morphFrom',
      'morphToFund', // تأكدنا من اسم العلاقة الصحيح
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
    $transaction->loadMorph('morphToFund', $morphRelationsMap); // تم التصحيح هنا

    return $transaction;
  }

  public function create(array $data): Transaction
  {
    return DB::transaction(function () use ($data) {
      $data['created_by'] = auth()->id();

      $transaction = Transaction::create($data);
      $transaction->load(['user', 'creator', 'morphFrom', 'morphToFund']);

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
      $transaction->load(['user', 'creator', 'morphFrom', 'morphToFund']);

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
