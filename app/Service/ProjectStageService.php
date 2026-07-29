<?php

namespace App\Service;

use App\Models\ProjectStage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectStageService
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
          ->orWhere('status', 'like', "%{$value}%");
      }),
      AllowedFilter::exact('project_id'),
      AllowedFilter::exact('status'),
    ];

    $query = QueryBuilder::for(ProjectStage::class)
      ->with(['project'])
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

  public function findOne(ProjectStage $projectStage): ProjectStage
  {
    return $projectStage->load(['project']);
  }

  public function create(array $data): ProjectStage
  {
    return DB::transaction(function () use ($data) {
      $stage = ProjectStage::create($data);

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء مرحلة مشروع جديدة: {$stage->name}",
        affectedTable: 'project_stages'
      );

      return $stage->load(['project']);
    });
  }

  public function update(ProjectStage $projectStage, array $data): ProjectStage
  {
    DB::transaction(function () use ($projectStage, $data) {
      $projectStage->update($projectStage->load(['project']) ? $data : $data);

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات مرحلة المشروع: {$projectStage->name}",
        affectedTable: 'project_stages'
      );
    });

    return $projectStage->load(['project']);
  }

  public function delete(ProjectStage $projectStage): bool
  {
    return DB::transaction(function () use ($projectStage) {
      $stageName = $projectStage->name ?? 'غير معروف';

      $deleted = (bool) $projectStage->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف مرحلة المشروع: {$stageName}",
          affectedTable: 'project_stages'
        );
      }

      return $deleted;
    });
  }
}
