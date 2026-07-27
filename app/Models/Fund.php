<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['user_id', 'name'])]
class Fund extends Model
{
  use HasFactory;

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function expenses(): MorphMany
  {
    return $this->morphMany(Expense::class, 'expenseable');
  }

  public function currencies(): BelongsToMany
  {
    return $this->belongsToMany(Currency::class, 'currency_funds')
      ->withPivot(['id', 'balance'])
      ->withTimestamps();
  }
}
