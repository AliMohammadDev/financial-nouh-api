<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
  'employee_id',
  'date',
  'amount',
  'reason',
])]
class Increment extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'amount' => 'decimal:2',
      'date'   => 'date',
    ];
  }

  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class);
  }
}
