<?php

namespace App\Service;

use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Validation\ValidationException;

class ItemService
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
        $query->where('name', 'like', "%{$value}%")
          ->orWhere('description', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Item::class)
      ->with('materials')
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

  public function findOne(Item $item): Item
  {
    return $item->load('materials');
  }

  public function create(array $data): Item
  {
    return DB::transaction(function () use ($data) {
      $item = Item::create($data);

      $itemName = $item->name ?? 'صنف';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء صنف جديد: {$itemName}",
        affectedTable: 'items'
      );

      return $item;
    });
  }

  public function update(Item $item, array $data): Item
  {
    DB::transaction(function () use ($item, $data) {
      $item->update($data);

      $itemName = $item->name ?? 'صنف';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الصنف: {$itemName}",
        affectedTable: 'items'
      );
    });

    return $item->load('materials');
  }

  public function delete(Item $item): bool
  {

    if ($item->materials()->exists()) {
      throw ValidationException::withMessages([
        'item' => ['لا يمكن حذف هذا البند لأنه يحتوي على مواد مرتبطة به.'],
      ]);
    }

    if ($item->invoices()->exists()) {

      throw ValidationException::withMessages([
        'item' => ['لا يمكن حذف هذا البند لأنه يحتوي على بفواتير موجودة .'],
      ]);
    }

    return DB::transaction(function () use ($item) {
      $itemName = $item->name ?? 'صنف';

      $deleted = (bool) $item->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الصنف: {$itemName}",
          affectedTable: 'items'
        );
      }

      return $deleted;
    });
  }
}
