<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'job_title', 'base_salary', 'department_id'])]
class Engineer extends Model
{
  use HasFactory;

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function department(): BelongsTo
  {
    return $this->belongsTo(Department::class);
  }
}
