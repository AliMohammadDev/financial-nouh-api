<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['project_fund_id', 'currency_id', 'balance'])]
class ProjectFundCurrency extends Pivot
{
  protected $table = 'project_fund_currencies';

  public function projectFund(): BelongsTo
  {
    return $this->belongsTo(ProjectFund::class);
  }

  public function currency(): BelongsTo
  {
    return $this->belongsTo(Currency::class);
  }
}
