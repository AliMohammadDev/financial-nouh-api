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
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $query = AuditLog::with('user')
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
