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
      ->withPivot(['id', 'balance'])
      ->withTimestamps();
  }

  public function projectFunds(): BelongsToMany
  {
    return $this->belongsToMany(ProjectFund::class, 'project_fund_currencies')
      ->using(ProjectFundCurrency::class)
      ->withPivot(['id', 'balance'])
      ->withTimestamps();
  }

  public function companyFunds(): BelongsToMany
  {
    return $this->belongsToMany(CompanyFund::class, 'company_fund_currencies')
      ->using(CompanyFundCurrency::class)
      ->withPivot(['id', 'balance'])
      ->withTimestamps();
  }
}
