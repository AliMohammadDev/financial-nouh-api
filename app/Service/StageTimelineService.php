<?php

namespace App\Service;

use App\Models\StageTimeline;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class StageTimelineService
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
        $query->where('stage_name', 'like', "%{$value}%")
          ->orWhere('status', 'like', "%{$value}%");
      }),
      AllowedFilter::exact('project_stage_id'),
      AllowedFilter::exact('status'),
      AllowedFilter::callback('project_id', function ($query, $value) {
        $query->whereHas('projectStage.project', function ($q) use ($value) {
          $q->where('project_id', $value);
        });
      }),
    ];

    $query = QueryBuilder::for(StageTimeline::class)
      ->with(['projectStage.project'])
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
  public function findOne(StageTimeline $stageTimeline): StageTimeline
  {
    return $stageTimeline->load(['projectStage.project']);
  }

  public function create(array $data): StageTimeline
  {
    return DB::transaction(function () use ($data) {
      $timeline = StageTimeline::create($data);
      $timeline->load(['projectStage.project']);

      $stageName = $timeline->stage_name ?? 'الخط الزمني';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإنشاء خط زمني جديد للمرحلة: {$stageName}",
        affectedTable: 'stage_timelines'
      );

      return $timeline;
    });
  }

  public function update(StageTimeline $stageTimeline, array $data): StageTimeline
  {
    DB::transaction(function () use ($stageTimeline, $data) {
      $stageTimeline->update($data);
      $stageTimeline->load(['projectStage.project']);

      $stageName = $stageTimeline->stage_name ?? 'الخط الزمني';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات الخط الزمني للمرحلة: {$stageName}",
        affectedTable: 'stage_timelines'
      );
    });

    return $stageTimeline->load(['projectStage.project']);
  }

  public function delete(StageTimeline $stageTimeline): bool
  {
    return DB::transaction(function () use ($stageTimeline) {
      $stageName = $stageTimeline->stage_name ?? 'الخط الزمني';

      $deleted = (bool) $stageTimeline->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بحذف الخط الزمني للمرحلة: {$stageName}",
          affectedTable: 'stage_timelines'
        );
      }

      return $deleted;
    });
  }
}
