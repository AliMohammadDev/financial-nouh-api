<?php

namespace App\Service;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
  public function getUserNotifications(
    User $user,
    bool $paginate = false,
    int $perPage = 10,
    int $page = 1,
    array $columns = ["*"]
  ): LengthAwarePaginator|Collection {

    $query = $user->notifications();

    if ($paginate) {
      return $query->paginate(
        perPage: $perPage,
        page: $page,
        columns: $columns,
      );
    }

    return $query->get($columns);
  }

  public function markAsRead(User $user, $notificationId): bool
  {
    $notification = $user->notifications()->where('id', $notificationId)->first();

    if ($notification) {
      $notification->markAsRead();
      return true;
    }

    return false;
  }
}
