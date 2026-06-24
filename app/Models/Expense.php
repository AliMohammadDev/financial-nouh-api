<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['expenseable_type', 'expenseable_id', 'description', 'amount', 'is_posted', 'employee_id', 'user_id'])]
class Expense extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'is_posted' => 'boolean',
    ];
  }

  public function expenseable(): MorphTo
  {
    return $this->morphTo();
  }

  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class, 'employee_id');
  }

  public function createdBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }
}
