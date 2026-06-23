<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'bonuses', 'deductions', 'payment_date', 'amount'])]
class EmployeePayment extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'payment_date' => 'date',
    ];
  }

  public function employee(): BelongsTo
  {
    return $this->belongsTo(Employee::class);
  }
}
