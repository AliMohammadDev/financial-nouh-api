<?php

namespace App\Service;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\Expense;
use App\Models\ProjectFundCurrency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ExpenseService
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
            ->orWhere('email', 'like', "%{$value}%");
        });
      }),

      AllowedFilter::callback('fund_id', function ($query, $value) {
        $query->where('expenseable_type', CurrencyFund::class)
          ->whereHasMorph('expenseable', [CurrencyFund::class], function ($q) use ($value) {
            $q->where('fund_id', $value);
          });
      }),

      AllowedFilter::callback('company_fund_id', function ($query, $value) {
        $query->where('expenseable_type', CompanyFundCurrency::class)
          ->whereHasMorph('expenseable', [CompanyFundCurrency::class], function ($q) use ($value) {
            $q->where('company_fund_id', $value);
          });
      }),

      AllowedFilter::callback('project_fund_id', function ($query, $value) {
        $query->where('expenseable_type', ProjectFundCurrency::class)
          ->whereHasMorph('expenseable', [ProjectFundCurrency::class], function ($q) use ($value) {
            $q->where('project_fund_id', $value);
          });
      }),

      AllowedFilter::callback('currency_fund_id', function ($query, $value) {
        $query->where('expenseable_type', CurrencyFund::class)
          ->where('expenseable_id', $value);
      }),
      AllowedFilter::callback('company_fund_currency_id', function ($query, $value) {
        $query->where('expenseable_type', CompanyFundCurrency::class)
          ->where('expenseable_id', $value);
      }),
      AllowedFilter::callback('project_fund_currency_id', function ($query, $value) {
        $query->where('expenseable_type', ProjectFundCurrency::class)
          ->where('expenseable_id', $value);
      }),
    ];

    $query = QueryBuilder::for(Expense::class)
      ->with([
        'user.client',
        'user.employee',
        'user.admin',
        'user.engineer',
        'user.craftsmen',
        'user.supplier',
        'user.trustee',
        'expenseable',
        'creator',
      ])
      ->allowedFilters(...$filters)
      ->defaultSort('-created_at');

    if ($paginate) {
      $paginator = $query->paginate(perPage: $perPage, page: $page, columns: $columns);

      $paginator->getCollection()->loadMorph('expenseable', [
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
      ]);

      return $paginator;
    }

    $expenses = $query->get($columns);

    $expenses->loadMorph('expenseable', [
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
    ]);

    return $expenses;
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
      'user.investor',
      'user.dailyWorker',
      'creator',
      'invoices.item',    
      'invoices.supplier',
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
        'fund.user.investor',
        'fund.user.dailyWorker',
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
      $expense->load(['user', 'creator', 'expenseable']);

      $amount = $expense->amount ?? 'مبلغ';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء سجل مصروف جديد بقيمة: {$amount}",
        affectedTable: 'expenses'
      );

      return $expense;
    });
  }

  public function update(Expense $expense, array $data): Expense
  {
    return DB::transaction(function () use ($expense, $data) {
      $expense->update($data);
      $expense->load(['user', 'creator', 'expenseable']);

      $amount = $expense->amount ?? 'مبلغ';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات سجل المصروف (القيمة: {$amount})",
        affectedTable: 'expenses'
      );

      return $expense;
    });
  }

  public function delete(Expense $expense): bool
  {
    return DB::transaction(function () use ($expense) {
      $amount = $expense->amount ?? 'مبلغ';

      $deleted = (bool) $expense->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف سجل المصروف (القيمة السابقة: {$amount})",
          affectedTable: 'expenses'
        );
      }

      return $deleted;
    });
  }
}