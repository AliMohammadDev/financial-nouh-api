<?php

namespace App\Service;

use App\Models\ProjectTeam;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class ProjectTeamService
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
        $query->where('name', 'like', "%{$value}%");
      }),
      AllowedFilter::exact('project_id'),
      AllowedFilter::exact('user_id'),
    ];

    $query = QueryBuilder::for(ProjectTeam::class)
      ->select('project_teams.*')
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
      ->allowedSorts(
        'created_at',
        'id',
        'name',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'project_teams.user_id', '=', 'users.id')
            ->orderBy('users.name', $direction);
        }),
        AllowedSort::callback('project_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('projects', 'project_teams.project_id', '=', 'projects.id')
            ->orderBy('projects.name', $direction);
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

  public function findOne(ProjectTeam $projectsTeam): ProjectTeam
  {
    return $projectsTeam->load(['user', 'project']);
  }

  public function create(array $data): ProjectTeam
  {
    return DB::transaction(function () use ($data) {
      $projectTeam = ProjectTeam::create($data);
      $projectTeam->load(['user', 'project']);

      $userName = $projectTeam->user?->name ?? 'مستخدم';
      $projectName = $projectTeam->project?->name ?? 'مشروع';

      $this->auditLogService->log(
        actionType: 'إضافة',
        description: "قام بإضافة العضو ({$userName}) إلى فريق المشروع: {$projectName}",
        affectedTable: 'project_teams'
      );

      return $projectTeam;
    });
  }

  public function update(ProjectTeam $projectsTeam, array $data): ProjectTeam
  {
    return DB::transaction(function () use ($projectsTeam, $data) {
      $projectsTeam->update($data);
      $projectsTeam->load(['user', 'project']);

      $userName = $projectsTeam->user?->name ?? 'مستخدم';
      $projectName = $projectsTeam->project?->name ?? 'مشروع';

      $this->auditLogService->log(
        actionType: 'تعديل',
        description: "قام بتعديل بيانات عضو فريق المشروع ({$userName}) في المشروع: {$projectName}",
        affectedTable: 'project_teams'
      );

      return $projectsTeam;
    });
  }

  public function delete(ProjectTeam $projectsTeam): bool
  {
    return DB::transaction(function () use ($projectsTeam) {
      $projectsTeam->load(['user', 'project']);
      $userName = $projectsTeam->user?->name ?? 'مستخدم';
      $projectName = $projectsTeam->project?->name ?? 'مشروع';

      $deleted = (bool) $projectsTeam->delete();

      if ($deleted) {
        $this->auditLogService->log(
          actionType: 'حذف',
          description: "قام بإزالة العضو ({$userName}) من فريق المشروع: {$projectName}",
          affectedTable: 'project_teams'
        );
      }

      return $deleted;
    });
  }
}
