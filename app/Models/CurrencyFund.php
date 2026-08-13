<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Fillable(['currency_id', 'fund_id', 'balance'])]
class CurrencyFund extends Pivot
{
  protected $table = 'currency_funds';
  protected $primaryKey = 'id'; // تحديد المفتاح الأساسي صراحةً
  public $incrementing = true;


  public function currency(): BelongsTo
  {
    return $this->belongsTo(Currency::class);
  }

  public function fund(): BelongsTo
  {
    return $this->belongsTo(Fund::class);
  }
}
