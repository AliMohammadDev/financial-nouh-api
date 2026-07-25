<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
  'name',
  'start_date',
  'expected_end_date',
  'actual_end_date',
  'status',
  'stage_progress',
  'project_id',
])]
class ProjectStage extends Model
{
  use HasFactory;

  protected $table = 'project_stages';

  protected function casts(): array
  {
    return [
      'start_date' => 'date',
      'expected_end_date' => 'date',
      'actual_end_date' => 'date',
      'stage_progress' => 'integer',
    ];
  }

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }
}
