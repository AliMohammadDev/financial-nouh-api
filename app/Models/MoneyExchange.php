<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
  'exchangeable_type',
  'exchangeable_id',
  'from_currency',
  'to_currency',
  'amount',
  'exchange_rate',
  'created_by',
])]
class MoneyExchange extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'exchange_rate' => 'decimal:4',
    ];
  }

  public function exchangeable(): MorphTo
  {
    return $this->morphTo();
  }

  public function fromCurrency(): BelongsTo
  {
    return $this->belongsTo(Currency::class, 'from_currency');
  }

  public function toCurrency(): BelongsTo
  {
    return $this->belongsTo(Currency::class, 'to_currency');
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
