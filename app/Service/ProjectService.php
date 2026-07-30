<?php

namespace App\Service;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Validation\ValidationException;

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
    ];

    $query = QueryBuilder::for(Project::class)
      ->with(['client.user', 'projectFunds', 'department'])
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
        'project' => ['You have to delete project teams first before deleting this project.'],
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
