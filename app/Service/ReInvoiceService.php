<?php

namespace App\Service;

use App\Models\CompanyFundCurrency;
use App\Models\CurrencyFund;
use App\Models\ProjectFundCurrency;
use App\Models\ReInvoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReInvoiceService
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
        $query->where('reinvoice_number', 'like', "%{$value}%")
          ->orWhereHas('item', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
          })
          ->orWhereHas('supplier', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
          });
      }),
      AllowedFilter::exact('item_id'),
      AllowedFilter::exact('supplier_id'),
      AllowedFilter::exact('is_posted'),
      AllowedFilter::exact('is_visible_to_client'),

      AllowedFilter::callback('fund_id', function ($query, $value) {
        $query->where('reinvoiceable_type', CurrencyFund::class)
          ->whereHasMorph('reinvoiceable', [CurrencyFund::class], function ($q) use ($value) {
            $q->where('fund_id', $value);
          });
      }),

      AllowedFilter::callback('company_fund_id', function ($query, $value) {
        $query->where('reinvoiceable_type', CompanyFundCurrency::class)
          ->whereHasMorph('reinvoiceable', [CompanyFundCurrency::class], function ($q) use ($value) {
            $q->where('company_fund_id', $value);
          });
      }),

      AllowedFilter::callback('project_fund_id', function ($query, $value) {
        $query->where('reinvoiceable_type', ProjectFundCurrency::class)
          ->whereHasMorph('reinvoiceable', [ProjectFundCurrency::class], function ($q) use ($value) {
            $q->where('project_fund_id', $value);
          });
      }),

      AllowedFilter::callback('currency_fund_id', function ($query, $value) {
        $query->where('reinvoiceable_type', CurrencyFund::class)
          ->where('reinvoiceable_id', $value);
      }),
      AllowedFilter::callback('company_fund_currency_id', function ($query, $value) {
        $query->where('reinvoiceable_type', CompanyFundCurrency::class)
          ->where('reinvoiceable_id', $value);
      }),
      AllowedFilter::callback('project_fund_currency_id', function ($query, $value) {
        $query->where('reinvoiceable_type', ProjectFundCurrency::class)
          ->where('reinvoiceable_id', $value);
      }),
    ];

    $query = QueryBuilder::for(ReInvoice::class)
      ->with(['item', 'supplier', 'reinvoiceable'])
      ->allowedFilters(...$filters)
      ->defaultSort('-created_at');

    $morphRelations = [
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
      $paginator->getCollection()->loadMorph('reinvoiceable', $morphRelations);
      return $paginator;
    }

    $reInvoices = $query->get($columns);
    $reInvoices->loadMorph('reinvoiceable', $morphRelations);

    return $reInvoices;
  }

  public function findOne(ReInvoice $reInvoice): ReInvoice
  {
    $reInvoice->load([
      'item',
      'supplier.client',
      'supplier.employee',
      'supplier.admin',
      'supplier.engineer',
      'supplier.craftsmen',
      'supplier.supplier',
      'supplier.trustee',
      'supplier.investor',
      'supplier.dailyWorker',
    ]);

    $reInvoice->loadMorph('reinvoiceable', [
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

    return $reInvoice;
  }

  public function create(array $data): ReInvoice
  {
    return DB::transaction(function () use ($data) {
      $year = date('Y');
      $month = date('m');

      $lastReInvoice = ReInvoice::whereYear('created_at', $year)
        ->whereMonth('created_at', $month)
        ->latest('id')
        ->lockForUpdate()
        ->first();

      $sequence = 1;
      if ($lastReInvoice) {
        $parts = explode('-', $lastReInvoice->reinvoice_number);
        $lastSequence = end($parts);
        $sequence = (int) $lastSequence + 1;
      }

      $formattedSequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);
      $data['reinvoice_number'] = "REV-{$year}-{$month}-{$formattedSequence}";

      $reInvoice = ReInvoice::create($data);
      $reInvoice->load(['item', 'supplier', 'reinvoiceable']);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء فاتورة مرتجع جديدة برقم: {$reInvoice->reinvoice_number}",
        affectedTable: 're_invoices'
      );

      return $reInvoice;
    });
  }

  public function update(ReInvoice $reInvoice, array $data): ReInvoice
  {
    return DB::transaction(function () use ($reInvoice, $data) {
      $reInvoice->update($data);
      $reInvoice->load(['item', 'supplier', 'reinvoiceable']);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل الفاتورة برقم: {$reInvoice->reinvoice_number}",
        affectedTable: 're_invoices'
      );

      return $reInvoice;
    });
  }

  public function delete(ReInvoice $reInvoice): bool
  {
    return DB::transaction(function () use ($reInvoice) {
      $invoiceNumber = $reInvoice->reinvoice_number;
      $deleted = (bool) $reInvoice->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الفاتورة برقم: {$invoiceNumber}",
          affectedTable: 're_invoices'
        );
      }

      return $deleted;
    });
  }
}
