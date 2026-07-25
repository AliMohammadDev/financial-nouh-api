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
    ];

    $query = QueryBuilder::for(StageTimeline::class)
      ->with(['projectStage'])
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
    return $stageTimeline->load(['projectStage']);
  }

  public function create(array $data): StageTimeline
  {
    return DB::transaction(function () use ($data) {
      $timeline = StageTimeline::create($data);
      return $timeline->load(['projectStage']);
    });
  }

  public function update(StageTimeline $stageTimeline, array $data): StageTimeline
  {
    DB::transaction(function () use ($stageTimeline, $data) {
      $stageTimeline->update($data);
    });

    return $stageTimeline->load(['projectStage']);
  }

  public function delete(StageTimeline $stageTimeline): bool
  {
    return DB::transaction(function () use ($stageTimeline) {
      return (bool) $stageTimeline->delete();
    });
  }
}
