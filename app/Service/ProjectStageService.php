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
      return $stage->load(['project']);
    });
  }

  public function update(ProjectStage $projectStage, array $data): ProjectStage
  {
    DB::transaction(function () use ($projectStage, $data) {
      $projectStage->update($data);
    });

    return $projectStage->load(['project']);
  }

  public function delete(ProjectStage $projectStage): bool
  {
    return DB::transaction(function () use ($projectStage) {
      return (bool) $projectStage->delete();
    });
  }
}
