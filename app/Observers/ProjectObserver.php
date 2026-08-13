<?php

namespace App\Observers;

use App\Models\Project;

class ProjectObserver
{
  /**
   * Handle the Project "created" event.
   */
  public function created(Project $project): void
  {
    $project->projectFunds()->create([
      'name'        => 'الصندوق الأساسي',
      'status'      => 'active',
      'threshold'   => 1000,
      'description' => 'صندوق المشروع: ' . $project->name,
    ])->currencies()->attach([
      1 => ['balance' => 0],
      2 => ['balance' => 0],
    ]);
  }

  /**
   * Handle the Project "updated" event.
   */
  public function updated(Project $project): void
  {
    //
  }

  /**
   * Handle the Project "deleted" event.
   */
  public function deleted(Project $project): void
  {
    //
  }

  /**
   * Handle the Project "restored" event.
   */
  public function restored(Project $project): void
  {
    //
  }

  /**
   * Handle the Project "force deleted" event.
   */
  public function forceDeleted(Project $project): void
  {
    //
  }
}
