<?php

namespace App\Service;

use App\Models\InvoiceItem;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class InvoiceItemService
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
      AllowedFilter::exact('invoice_id'),
      AllowedFilter::exact('material_id'),
    ];

    $query = QueryBuilder::for(InvoiceItem::class)
      ->with(['material', 'invoice'])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'item_description',
        'unit',
        'quantity',
        'unit_price',
        'total_price',
        AllowedSort::callback('material_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('materials', 'invoice_items.material_id', '=', 'materials.id')
            ->orderBy('materials.name', $direction);
        })
      )
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

  public function findOne(InvoiceItem $invoiceItem): InvoiceItem
  {
    return $invoiceItem->load(['material', 'invoice']);
  }

  public function create(array $data): InvoiceItem
  {
    return DB::transaction(function () use ($data) {
      $invoiceItem = InvoiceItem::create($data);
      $invoiceItem->load(['material', 'invoice']);

      $itemDesc = $invoiceItem->item_description ?? 'عنصر فاتورة';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء عنصر جديد في الفاتورة: {$itemDesc}",
        affectedTable: 'invoice_items'
      );

      return $invoiceItem;
    });
  }

  public function update(InvoiceItem $invoiceItem, array $data): InvoiceItem
  {
    return DB::transaction(function () use ($invoiceItem, $data) {
      $invoiceItem->update($data);
      $invoiceItem->load(['material', 'invoice']);

      $itemDesc = $invoiceItem->item_description ?? 'عنصر فاتورة';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات عنصر الفاتورة: {$itemDesc}",
        affectedTable: 'invoice_items'
      );

      return $invoiceItem;
    });
  }

  public function delete(InvoiceItem $invoiceItem): bool
  {
    return DB::transaction(function () use ($invoiceItem) {
      $invoiceItem->load(['material', 'invoice']);
      $itemDesc = $invoiceItem->item_description ?? 'عنصر فاتورة';

      $deleted = (bool) $invoiceItem->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف عنصر الفاتورة: {$itemDesc}",
          affectedTable: 'invoice_items'
        );
      }

      return $deleted;
    });
  }
}
