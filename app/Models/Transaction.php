<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
  'morph_from_type',
  'morph_from_id',
  'morph_to_type',
  'morph_to_id',
  'name',
  'amount',
  'created_by',
])]
class Transaction extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'amount' => 'decimal:2',
    ];
  }

  public function morphFrom(): MorphTo
  {
    return $this->morphTo();
  }

  public function morphToFund(): MorphTo
  {
    return $this->morphTo('morphToFund', 'morph_to_type', 'morph_to_id');
  }


  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
