<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['currency', 'symbol'])]
class Currency extends Model
{
  use HasFactory;
  public function funds(): BelongsToMany
  {
    return $this->belongsToMany(Fund::class, 'currency_funds')
      ->using(CurrencyFund::class)
      ->withPivot(['balance'])
      ->withTimestamps();
  }
}
