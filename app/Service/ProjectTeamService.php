<?php

namespace App\Service;

use App\Models\ProjectTeam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectTeamService
{
  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::callback('search', function ($query, $value) {
        $query->where('name', 'like', "%{$value}%");
      }),
      AllowedFilter::exact('project_id'),
      AllowedFilter::exact('user_id'),
    ];

    $query = QueryBuilder::for(ProjectTeam::class)
      ->with([
        'user.admin',
        'user.client',
        'user.employee',
        'user.craftsmen',
        'user.supplier',
        'user.trustee',
        'project'
      ])
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

  public function findOne(ProjectTeam $projectsTeam): ProjectTeam
  {
    return $projectsTeam->load(['user', 'project']);
  }

  public function create(array $data): ProjectTeam
  {
    return DB::transaction(function () use ($data) {
      return ProjectTeam::create($data);
    });
  }

  public function update(ProjectTeam $projectsTeam, array $data): ProjectTeam
  {
    return DB::transaction(function () use ($projectsTeam, $data) {
      $projectsTeam->update($data);
      return $projectsTeam->load(['user', 'project']);
    });
  }

  public function delete(ProjectTeam $projectsTeam): bool
  {
    return DB::transaction(function () use ($projectsTeam) {
      return (bool) $projectsTeam->delete();
    });
  }
}
