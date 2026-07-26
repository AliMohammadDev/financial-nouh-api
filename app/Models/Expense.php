<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
  'expenseable_type',
  'expenseable_id',
  'description',
  'amount',
  'is_posted',
  'user_id',
  'created_by',
])]
class Expense extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'is_posted' => 'boolean',
      'amount'    => 'decimal:2',
    ];
  }

  public function expenseable(): MorphTo
  {
    return $this->morphTo();
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
