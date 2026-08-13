<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['client_id', 'name', 'expected_cost', 'status', 'is_locked'])]
class Project extends Model
{
  use HasFactory;

  public function client(): BelongsTo
  {
    return $this->belongsTo(Client::class);
  }


  public function projectFunds(): HasMany
  {
    return $this->hasMany(ProjectFund::class);
  }

  public function projectTeams(): HasMany
  {
    return $this->hasMany(ProjectTeam::class, 'project_id');
  }

  public function departments(): BelongsToMany
  {
    return $this->belongsToMany(Department::class, 'department_projects');
  }
}