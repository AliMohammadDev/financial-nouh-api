<?php

namespace App\Service;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\MoneyExchange;
use App\Models\ProjectFundCurrency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class MoneyExchangeService
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
        $query->where('amount', 'like', "%{$value}%")
          ->orWhere('converted_amount', 'like', "%{$value}%");
      }),
      AllowedFilter::exact('from_currency'),
      AllowedFilter::exact('to_currency'),
      AllowedFilter::exact('operation'),

      AllowedFilter::callback('date_from', function ($query, $value) {
        $query->whereDate('created_at', '>=', $value);
      }),
      AllowedFilter::callback('date_to', function ($query, $value) {
        $query->whereDate('created_at', '<=', $value);
      }),

      AllowedFilter::callback('currency_fund_id', function ($query, $value) {
        $query->where('exchangeable_type', CurrencyFund::class)
          ->where('exchangeable_id', $value);
      }),
      AllowedFilter::callback('company_fund_currency_id', function ($query, $value) {
        $query->where('exchangeable_type', CompanyFundCurrency::class)
          ->where('exchangeable_id', $value);
      }),
      AllowedFilter::callback('project_fund_currency_id', function ($query, $value) {
        $query->where('exchangeable_type', ProjectFundCurrency::class)
          ->where('exchangeable_id', $value);
      }),
    ];

    $query = QueryBuilder::for(MoneyExchange::class)
      ->select('money_exchanges.*')
      ->with([
        'fromCurrency',
        'toCurrency',
        'exchangeable',
        'creator.client',
        'creator.employee',
        'creator.admin',
        'creator.engineer',
        'creator.craftsmen',
        'creator.supplier',
        'creator.trustee',
        'creator.investor',
        'creator.dailyWorker',
      ])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        AllowedSort::field('created_at', 'money_exchanges.created_at'),
        'id',
        'exchange_rate',
        'converted_amount',
        'operation',
        AllowedSort::callback('creator_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users as c', 'money_exchanges.created_by', '=', 'c.id')
            ->orderBy('c.name', $direction);
        })
      )
      ->defaultSort('-money_exchanges.created_at');


    if ($paginate) {
      $paginator = $query->paginate(perPage: $perPage, page: $page, columns: $columns);

      $paginator->getCollection()->loadMorph('exchangeable', [
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

    $exchanges = $query->get($columns);

    $exchanges->loadMorph('exchangeable', [
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

    return $exchanges;
  }

  public function findOne(MoneyExchange $moneyExchange): MoneyExchange
  {
    $moneyExchange->load([
      'fromCurrency',
      'toCurrency',
      'creator.client',
      'creator.employee',
      'creator.admin',
      'creator.engineer',
      'creator.craftsmen',
      'creator.supplier',
      'creator.trustee',
      'creator.investor',
      'creator.dailyWorker',
    ]);

    $moneyExchange->loadMorph('exchangeable', [
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

    return $moneyExchange;
  }

  public function create(array $data): MoneyExchange
  {
    return DB::transaction(function () use ($data) {
      $data['created_by'] = auth()->id();
      $moneyExchange = MoneyExchange::create($data);
      $moneyExchange->load(['fromCurrency', 'toCurrency', 'creator', 'exchangeable']);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء عملية تصريف/تحويل أموال جديدة برقم: {$moneyExchange->id}",
        affectedTable: 'money_exchanges'
      );

      return $moneyExchange;
    });
  }

  public function update(MoneyExchange $moneyExchange, array $data): MoneyExchange
  {
    return DB::transaction(function () use ($moneyExchange, $data) {
      $moneyExchange->update($data);
      $moneyExchange->load(['fromCurrency', 'toCurrency', 'creator', 'exchangeable']);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل عملية تصريف/تحويل أموال برقم: {$moneyExchange->id}",
        affectedTable: 'money_exchanges'
      );

      return $moneyExchange;
    });
  }

  public function delete(MoneyExchange $moneyExchange): bool
  {
    return DB::transaction(function () use ($moneyExchange) {
      $exchangeId = $moneyExchange->id;
      $deleted = (bool) $moneyExchange->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف عملية تصريف/تحويل أموال برقم: {$exchangeId}",
          affectedTable: 'money_exchanges'
        );
      }

      return $deleted;
    });
  }
}
