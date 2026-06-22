<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['project_id', 'fund_id'])]
class ProjectFund extends Model
{
  use HasFactory;

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function fund(): BelongsTo
  {
    return $this->belongsTo(Fund::class);
  }
}
