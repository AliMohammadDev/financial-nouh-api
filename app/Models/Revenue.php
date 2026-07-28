<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
  'revenueable_type',
  'revenueable_id',
  'statement',
  'amount',
  'is_posted',
  'user_id',
  'received_by',
])]
class Revenue extends Model
{
  use HasFactory;

  protected $table = 'revenues';

  protected function casts(): array
  {
    return [
      'is_posted' => 'boolean',
      'amount'    => 'decimal:2',
    ];
  }

  public function revenueable(): MorphTo
  {
    return $this->morphTo();
  }

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class, 'user_id');
  }

  public function receiver(): BelongsTo
  {
    return $this->belongsTo(User::class, 'received_by');
  }


}