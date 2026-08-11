<?php

namespace App\Service;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\ProjectFundCurrency;
use App\Models\Revenue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;


class RevenueService
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
        $query->where('statement', 'like', "%{$value}%")
          ->orWhereHas('user', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
          });
      }),
      AllowedFilter::exact('is_posted'),
      AllowedFilter::exact('received_by'),
      AllowedFilter::callback('date_from', function ($query, $value) {
        $query->whereDate('created_at', '>=', $value);
      }),
      AllowedFilter::callback('date_to', function ($query, $value) {
        $query->whereDate('created_at', '<=', $value);
      }),

      AllowedFilter::callback('fund_id', function ($query, $value) {
        $query->where('revenueable_type', CurrencyFund::class)
          ->whereHasMorph('revenueable', [CurrencyFund::class], function ($q) use ($value) {
            $q->where('fund_id', $value);
          });
      }),

      AllowedFilter::callback('company_fund_id', function ($query, $value) {
        $query->where('revenueable_type', CompanyFundCurrency::class)
          ->whereHasMorph('revenueable', [CompanyFundCurrency::class], function ($q) use ($value) {
            $q->where('company_fund_id', $value);
          });
      }),

      AllowedFilter::callback('project_fund_id', function ($query, $value) {
        $query->where('revenueable_type', ProjectFundCurrency::class)
          ->whereHasMorph('revenueable', [ProjectFundCurrency::class], function ($q) use ($value) {
            $q->where('project_fund_id', $value);
          });
      }),

      AllowedFilter::callback('currency_fund_id', function ($query, $value) {
        $query->where('revenueable_type', CurrencyFund::class)
          ->where('revenueable_id', $value);
      }),
      AllowedFilter::callback('company_fund_currency_id', function ($query, $value) {
        $query->where('revenueable_type', CompanyFundCurrency::class)
          ->where('revenueable_id', $value);
      }),
      AllowedFilter::callback('project_fund_currency_id', function ($query, $value) {
        $query->where('revenueable_type', ProjectFundCurrency::class)
          ->where('revenueable_id', $value);
      }),
    ];

    $query = QueryBuilder::for(Revenue::class)
      ->select('revenues.*')
      ->with([
        'revenueable',
        'receiver.client',
        'receiver.employee',
        'receiver.admin',
        'receiver.engineer',
        'receiver.craftsmen',
        'receiver.supplier',
        'receiver.trustee',
        'receiver.investor',
        'receiver.dailyWorker',
      ])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        AllowedSort::field('created_at', 'revenues.created_at'),
        'id',
        'amount',
        'is_posted',
        AllowedSort::callback('receiver_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users as r', 'revenues.received_by', '=', 'r.id')
            ->orderBy('r.name', $direction);
        })
      )
      ->defaultSort('-created_at');

    if ($paginate) {
      $paginator = $query->paginate(perPage: $perPage, page: $page, columns: $columns);

      $paginator->getCollection()->loadMorph('revenueable', [
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

    $revenues = $query->get($columns);

    $revenues->loadMorph('revenueable', [
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

    return $revenues;
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
      'user.investor',
      'user.dailyWorker',

      'receiver.client',
      'receiver.employee',
      'receiver.admin',
      'receiver.engineer',
      'receiver.craftsmen',
      'receiver.supplier',
      'receiver.trustee',
      'receiver.investor',
      'receiver.dailyWorker',

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
        'fund.user.investor',
        'fund.user.dailyWorker',
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
      $revenue->load(['receiver', 'revenueable']);

      $amount = $revenue->amount ?? 'مبلغ';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء سجل إيراد جديد بقيمة: {$amount}",
        affectedTable: 'revenues'
      );

      return $revenue;
    });
  }

  public function update(Revenue $revenue, array $data): Revenue
  {
    return DB::transaction(function () use ($revenue, $data) {
      $revenue->update($data);
      $revenue->load(['receiver', 'revenueable']);

      $amount = $revenue->amount ?? 'مبلغ';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات سجل الإيراد (القيمة: {$amount})",
        affectedTable: 'revenues'
      );

      return $revenue;
    });
  }

  public function delete(Revenue $revenue): bool
  {
    return DB::transaction(function () use ($revenue) {
      $amount = $revenue->amount ?? 'مبلغ';

      $deleted = (bool) $revenue->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف سجل الإيراد (القيمة السابقة: {$amount})",
          affectedTable: 'revenues'
        );
      }

      return $deleted;
    });
  }
}
