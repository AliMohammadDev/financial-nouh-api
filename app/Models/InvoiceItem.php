<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
  'invoice_id',
  'material_id',
  'item_description',
  'unit',
  'quantity',
  'unit_price',
  'total_price',
])]
class InvoiceItem extends Model
{
  use HasFactory;

  protected function casts(): array
  {
    return [
      'quantity'    => 'decimal:2',
      'unit_price'  => 'decimal:2',
      'total_price' => 'decimal:2',
    ];
  }

  protected static function booted(): void
  {
    static::saving(function ($invoiceItem) {
      if (isset($invoiceItem->quantity) && isset($invoiceItem->unit_price)) {
        $invoiceItem->total_price = $invoiceItem->quantity * $invoiceItem->unit_price;
      }
    });
  }

  public function invoice(): BelongsTo
  {
    return $this->belongsTo(Invoice::class);
  }

  public function material(): BelongsTo
  {
    return $this->belongsTo(Material::class);
  }
}
