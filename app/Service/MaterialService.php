<?php

namespace App\Service;

use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Spatie\QueryBuilder\AllowedSort;

class MaterialService
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
          ->orWhere('description', 'like', "%{$value}%")
          ->orWhereHas('item', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%");
          });
      }),
    ];

    $query = QueryBuilder::for(Material::class)
      ->with('item')
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'name',
        'description',
        AllowedSort::callback('item_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('items', 'materials.item_id', '=', 'items.id')
            ->orderBy('items.name', $direction);
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

  public function findOne(Material $material): Material
  {
    return $material->load('item');
  }

  public function create(array $data): Material
  {
    return DB::transaction(function () use ($data) {
      $material = Material::create($data);

      $materialName = $material->name ?? 'مادة';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء مادة جديدة: {$materialName}",
        affectedTable: 'materials'
      );

      return $material->load('item');
    });
  }

  public function update(Material $material, array $data): Material
  {
    DB::transaction(function () use ($material, $data) {
      $material->update($data);

      $materialName = $material->name ?? 'مادة';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المادة: {$materialName}",
        affectedTable: 'materials'
      );
    });

    return $material->load('item');
  }

  public function delete(Material $material): bool
  {

    if ($material->invoiceItems()->exists()) {
      throw new HttpResponseException(
        response()->json([
          'message' => 'لا يمكن حذف هذه المادة لأنها مستخدمة في بنود فواتير سابقة.',
        ])
      );
    }
    return DB::transaction(function () use ($material) {
      $materialName = $material->name ?? 'مادة';

      $deleted = (bool) $material->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المادة: {$materialName}",
          affectedTable: 'materials'
        );
      }

      return $deleted;
    });
  }
}
