<?php

namespace App\Service;

use App\Models\Project;
use App\Models\ProjectFund;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectService
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
          ->orWhere('status', 'like', "%{$value}%")
          ->orWhereHas('client.user', function ($q) use ($value) {
            $q->where('name', 'like', "%{$value}%")
              ->orWhere('email', 'like', "%{$value}%");
          });
      }),
    ];

    $query = QueryBuilder::for(Project::class)
      ->with(['client.user', 'projectFunds'])
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
    return $project->load(['client.user', 'projectFunds']);
  }

  public function create(array $data): Project
  {
    return DB::transaction(function () use ($data) {
      $project = Project::create($data);
      return $project->load(['client.user', 'projectFunds']);
    });
  }

  public function update(Project $project, array $data): Project
  {
    DB::transaction(function () use ($project, $data) {
      $project->update($data);
    });

    return $project->load(['client.user', 'projectFunds']);
  }

  public function delete(Project $project): bool
  {
    return DB::transaction(function () use ($project) {
      return (bool) $project->delete();
    });
  }

  // project funds
  public function attachFund(Project $project, array $data): ProjectFund
  {
    return DB::transaction(function () use ($project, $data) {
      return ProjectFund::firstOrCreate([
        'project_id' => $project->id,
        'name'       => $data['name'] ?? 'Main Fund',
        'type'       => $data['type'] ?? 'general',
        'currency'   => $data['currency'] ?? 'usd',
      ]);
    });
  }

  public function detachFund(ProjectFund $projectFund): bool
  {
    return DB::transaction(function () use ($projectFund) {
      return (bool) $projectFund->delete();
    });
  }
}
