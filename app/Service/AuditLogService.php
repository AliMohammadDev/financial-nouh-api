<?php

namespace App\Service;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;

class AuditLogService
{
  public function log(
    string $actionType,
    string $description,
    ?string $affectedTable = null,
    ?int $userId = null
  ): AuditLog {
    return AuditLog::create([
      'user_id'        => $userId ?? auth()->id(),
      'action_type'    => $actionType,
      'affected_table' => $affectedTable,
      'description'    => $description,
      'created_at'     => now(),
    ]);
  }

  public function findAll(
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $filters = [
      AllowedFilter::exact('user_id'),
      AllowedFilter::partial('action_type'),
      AllowedFilter::partial('affected_table'),
      AllowedFilter::partial('description'),
      AllowedFilter::callback('created_at', function ($query, $value) {
        $query->whereDate('created_at', $value);
      }),
    ];

    $query = QueryBuilder::for(AuditLog::class)
      ->select('audit_logs.*')
      ->with('user')
      ->allowedFilters(...$filters)
      ->allowedSorts(
        'created_at',
        'id',
        'action_type',
        'affected_table',
        AllowedSort::callback('user_name', function ($query, $descending) {
          $direction = $descending ? 'desc' : 'asc';
          $query->join('users', 'audit_logs.user_id', '=', 'users.id')
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
}
