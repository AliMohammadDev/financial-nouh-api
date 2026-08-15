<?php

namespace App\Service;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class DepartmentService
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
        $query->where('Name', 'like', "%{$value}%")
          ->orWhere('Main_Manager', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Department::class)
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'Name',
        'Main_Manager'
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

  public function findOne(Department $department): Department
  {
    return $department->load(['employees.user.media', 'projects', 'engineers.user.media']);
  }

  public function create(array $data): Department
  {
    return DB::transaction(function () use ($data) {
      $department = Department::create($data);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء قسم جديد: {$department->Name}",
        affectedTable: 'departments'
      );

      return $department;
    });
  }

  public function update(Department $department, array $data): Department
  {
    return DB::transaction(function () use ($department, $data) {
      $department->update($data);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات القسم: {$department->Name}",
        affectedTable: 'departments'
      );

      return $department;
    });
  }

  public function delete(Department $department): bool
  {
    if ($department->projects()->exists()) {
      throw ValidationException::withMessages([
        'department' => ['You have to delete projects first before deleting this department.'],
      ]);
    }

    return DB::transaction(function () use ($department) {
      $departmentName = $department->Name ?? 'غير معروف';

      $deleted = (bool) $department->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف القسم: {$departmentName}",
          affectedTable: 'departments'
        );
      }

      return $deleted;
    });
  }
}
