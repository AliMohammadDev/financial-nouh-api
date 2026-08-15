<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
  'name',
  'status',
  'threshold',
  'description',
  'type'
])]
class CompanyFund extends Model
{
  use HasFactory;
  public function expenses(): MorphMany
  {
    return $this->morphMany(Expense::class, 'expenseable');
  }

  public function currencies(): BelongsToMany
  {
    return $this->belongsToMany(Currency::class, 'company_fund_currencies')
      ->using(CompanyFundCurrency::class)
      ->withPivot(['id', 'balance'])
      ->withTimestamps();
  }
}
