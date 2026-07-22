<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['company_fund_id', 'currency_id', 'balance'])]
class CompanyFundCurrency extends Pivot
{
  protected $table = 'company_fund_currencies';

  public function companyFund(): BelongsTo
  {
    return $this->belongsTo(CompanyFund::class);
  }

  public function currency(): BelongsTo
  {
    return $this->belongsTo(Currency::class);
  }
}
