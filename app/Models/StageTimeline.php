<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
  'stage_name',
  'start_date',
  'expected_end_date',
  'actual_end_date',
  'status',
  'stage_progress',
  'project_stage_id',
])]
class StageTimeline extends Model
{
  use HasFactory;

  protected $table = 'stage_timelines';

  protected function casts(): array
  {
    return [
      'start_date' => 'date',
      'expected_end_date' => 'date',
      'actual_end_date' => 'date',
      'stage_progress' => 'integer',
    ];
  }

  public function projectStage(): BelongsTo
  {
    return $this->belongsTo(ProjectStage::class);
  }
}
