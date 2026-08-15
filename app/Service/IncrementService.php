<?php

namespace App\Service;

use App\Models\Increment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IncrementService
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
        $query->where('type', 'like', "%{$value}%")
          ->orWhere('reason', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Increment::class)
      ->with(['employee.user.media'])
      ->allowedFilters(...$filters)
      ->allowedSorts('created_at', 'id', 'type', 'amount')
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

  public function findOne(Increment $increment): Increment
  {
    return $increment->load(['employee.user.media']);
  }

  public function create(array $data): Increment
  {
    return DB::transaction(function () use ($data) {
      $increment = Increment::create($data);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإضافة زيادة جديدة برقم: {$increment->id}",
        affectedTable: 'increments'
      );

      return $increment->load(['employee.user.media']);
    });
  }

  public function update(Increment $increment, array $data): Increment
  {
    return DB::transaction(function () use ($increment, $data) {
      $increment->update($data);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل الزيادة رقم: {$increment->id}",
        affectedTable: 'increments'
      );

      return $increment->load(['employee.user.media']);
    });
  }

  public function delete(Increment $increment): bool
  {
    return DB::transaction(function () use ($increment) {
      $incrementId = $increment->id;
      $deleted = (bool) $increment->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الزيادة رقم: {$incrementId}",
          affectedTable: 'increments'
        );
      }

      return $deleted;
    });
  }
}
