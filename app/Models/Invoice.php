<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
  'item_id',
  'expense_id',
  'supplier_id',
  'invoice_number',
  'date',
  'discount',
  'final_total',
  'is_posted',
  'is_visible_to_client',
])]
class Invoice extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'date'                 => 'datetime',
      'discount'             => 'decimal:2',
      'final_total'          => 'decimal:2',
      'is_posted'            => 'boolean',
      'is_visible_to_client' => 'boolean',
    ];
  }

  public function item(): BelongsTo
  {
    return $this->belongsTo(Item::class);
  }

  public function expense(): BelongsTo
  {
    return $this->belongsTo(Expense::class);
  }

  public function supplier(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function invoiceItems(): HasMany
  {
    return $this->hasMany(InvoiceItem::class);
  }
}
