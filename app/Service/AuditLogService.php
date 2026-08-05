<?php

namespace App\Service;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

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
    array $filters = [],
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $query = AuditLog::with('user')
      ->when(!empty($filters['user_id']), function ($q) use ($filters) {
        $q->where('user_id', $filters['user_id']);
      })
      ->when(!empty($filters['action_type']), function ($q) use ($filters) {
        $q->where('action_type', 'like', '%' . $filters['action_type'] . '%');
      })
      ->when(!empty($filters['affected_table']), function ($q) use ($filters) {
        $q->where('affected_table', 'like', '%' . $filters['affected_table'] . '%');
      })
      ->when(!empty($filters['description']), function ($q) use ($filters) {
        $q->where('description', 'like', '%' . $filters['description'] . '%');
      })
      ->when(!empty($filters['created_at']), function ($q) use ($filters) {
        $q->whereDate('created_at', $filters['created_at']);
      })
      ->latest('created_at');

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
