<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
  /**
   * Handle the User "created" event.
   */
  public function created(User $user): void
  {
    $user->funds()->create([
      'name' => 'الصندوق الأساسي',
      'is_locked' => false,
      'status' => 'active',
      'threshold' => 0,
      'description' => 'صندوق المستخدم: ' . $user->name,
    ]);
  }

  /**
   * Handle the User "updated" event.
   */
  public function updated(User $user): void
  {
    //
  }

  /**
   * Handle the User "deleted" event.
   */
  public function deleted(User $user): void
  {
    //
  }

  /**
   * Handle the User "restored" event.
   */
  public function restored(User $user): void
  {
    //
  }

  /**
   * Handle the User "force deleted" event.
   */
  public function forceDeleted(User $user): void
  {
    //
  }
}
