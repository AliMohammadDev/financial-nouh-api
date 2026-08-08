<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'main_manager'])]
class Department extends Model
{
  use HasFactory;

  public function projects(): BelongsToMany
  {
    return $this->belongsToMany(Project::class, 'department_projects');
  }
}
