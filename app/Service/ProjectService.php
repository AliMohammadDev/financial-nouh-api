<?php

namespace App\Service;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\AllowedSort;

class ProjectService
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
          ->orWhere('status', 'like', "%{$value}%")
          ->orWhereHas('client.user', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
          });
      }),
      AllowedFilter::exact('client_id'),
      AllowedFilter::exact('department_id'),
      AllowedFilter::exact('status'),
    ];

    $query = QueryBuilder::for(Project::class)
      ->select('projects.*')
      ->with(['client.user', 'projectFunds', 'department'])
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'name',
        'status',
        AllowedSort::callback('client_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('clients', 'projects.client_id', '=', 'clients.id')
            ->join('users', 'clients.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction);
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

  public function findOne(Project $project): Project
  {
    return $project->load(['client.user', 'projectFunds.currencies', 'department']);
  }

  public function create(array $data): Project
  {
    return DB::transaction(function () use ($data) {
      $project = Project::create($data);
      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء مشروع جديد: {$project->name}",
        affectedTable: 'projects'
      );

      return $project->load(['client.user', 'projectFunds']);
    });
  }

  public function update(Project $project, array $data): Project
  {
    DB::transaction(function () use ($project, $data) {
      $project->update($data);
      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات المشروع: {$project->name}",
        affectedTable: 'projects'
      );
    });

    return $project->load(['client.user', 'projectFunds']);
  }

  public function delete(Project $project): bool
  {
    if ($project->projectTeams()->exists()) {
      throw ValidationException::withMessages([
        'project' => ['يجب حذف فرق العمل المرتبطة بالمشروع أولاً قبل حذف هذا المشروع.'],
      ]);
    }

    return DB::transaction(function () use ($project) {
      $projectName = $project->name ?? 'غير معروف';

      $deleted = (bool) $project->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف المشروع: {$projectName}",
          affectedTable: 'projects'
        );
      }

      return $deleted;
    });
  }
}
