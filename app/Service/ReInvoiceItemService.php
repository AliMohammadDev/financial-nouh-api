<?php

namespace App\Service;

use App\Models\ReInvoiceItem;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ReInvoiceItemService
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
        $query->where('item_description', 'like', "%{$value}%")
          ->orWhere('unit', 'like', "%{$value}%")
          ->orWhereHas('material', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
          });
      }),
      AllowedFilter::exact('reinvoice_id'),
      AllowedFilter::exact('material_id'),
    ];

    $query = QueryBuilder::for(ReInvoiceItem::class)
      ->with(['material', 'reInvoice'])
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

  public function findOne(ReInvoiceItem $reInvoiceItem): ReInvoiceItem
  {
    return $reInvoiceItem->load(['material', 'reInvoice']);
  }

  public function create(array $data): ReInvoiceItem
  {
    return DB::transaction(function () use ($data) {
      $reInvoiceItem = ReInvoiceItem::create($data);
      $reInvoiceItem->load(['material', 'reInvoice']);

      $itemDesc = $reInvoiceItem->item_description ?? 'عنصر فاتورة مرتجعة';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء عنصر جديد في الفاتورة المرتجعة: {$itemDesc}",
        affectedTable: 're_invoice_items'
      );

      return $reInvoiceItem;
    });
  }

  public function update(ReInvoiceItem $reInvoiceItem, array $data): ReInvoiceItem
  {
    return DB::transaction(function () use ($reInvoiceItem, $data) {
      $reInvoiceItem->update($data);
      $reInvoiceItem->load(['material', 'reInvoice']);

      $itemDesc = $reInvoiceItem->item_description ?? 'عنصر فاتورة مرتجعة';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات عنصر الفاتورة المرتجعة: {$itemDesc}",
        affectedTable: 're_invoice_items'
      );

      return $reInvoiceItem;
    });
  }

  public function delete(ReInvoiceItem $reInvoiceItem): bool
  {
    return DB::transaction(function () use ($reInvoiceItem) {
      $reInvoiceItem->load(['material', 'reInvoice']);
      $itemDesc = $reInvoiceItem->item_description ?? 'عنصر فاتورة مرتجعة';

      $deleted = (bool) $reInvoiceItem->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف عنصر الفاتورة المرتجعة: {$itemDesc}",
          affectedTable: 're_invoice_items'
        );
      }

      return $deleted;
    });
  }
}
