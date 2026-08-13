<?php

namespace App\Service\User;

use App\Models\Employee;
use App\Models\User;
use App\Service\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeService
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
            ->orWhere('email', 'like', "%{$value}%")
            ->orWhere('phone_number', 'like', "%{$value}%");
        })->orWhere('job_title', 'like', "%{$value}%");
      }),
    ];

    $query = QueryBuilder::for(Employee::class)
      ->with([
        'user.funds.currencies',
        'user.media',
      ])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'job_title',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'employees.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction)
            ->select('employees.*');
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

  public function findOne(Employee $employee): Employee
  {
    return $employee->load(['user.funds.currencies', 'user.media']);
  }

  public function create(array $data, $imageFiles = null): Employee
  {
    return DB::transaction(function () use ($data, $imageFiles) {
      $data['password'] = Hash::make($data['password']);

      $user = User::create($data);

      if ($imageFiles) {
        $this->attachMedia($user, $imageFiles);
      }

      $employee = Employee::create([
        'user_id'   => $user->id,
        'job_title' => $data['job_title']
      ]);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بتسجيل موظف جديد: {$user->name} (المسمى الوظيفي: {$employee->job_title})",
        affectedTable: 'employees'
      );

      return $employee->load('user.media');
    });
  }

  public function update(Employee $employee, array $data, $imageFiles = null, array $deletedMediaIds = []): Employee
  {
    DB::transaction(function () use ($employee, $data, $imageFiles, $deletedMediaIds) {
      if (!empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        unset($data['password']);
      }

      $employee->user->update(collect($data)->except(['job_title', 'images', 'deleted_media_ids'])->toArray());

      if (isset($data['job_title'])) {
        $employee->update(collect($data)->only(['job_title'])->toArray());
      }

      if (!empty($deletedMediaIds)) {
        $mediaItems = $employee->user->media()->whereIn('id', $deletedMediaIds)->get();
        foreach ($mediaItems as $media) {
          $media->delete();
        }
      }

      if ($imageFiles) {
        $this->attachMedia($employee->user, $imageFiles);
      }

      $employee->load('user.media');

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الموظف: {$employee->user->name}",
        affectedTable: 'employees'
      );
    });

    return $employee->load('user.media');
  }

  public function delete(Employee $employee): bool
  {
    return DB::transaction(function () use ($employee) {
      $employeeName = $employee->user?->name ?? 'غير معروف';

      $deleted = (bool) $employee->user()->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الموظف: {$employeeName}",
          affectedTable: 'employees'
        );
      }

      return $deleted;
    });
  }

  private function attachMedia(User $user, $imageFiles)
  {
    $files = is_array($imageFiles) ? $imageFiles : [$imageFiles];

    foreach ($files as $file) {
      if ($file) {
        $user->addMedia($file)->toMediaCollection('users_files');
      }
    }
  }
}
