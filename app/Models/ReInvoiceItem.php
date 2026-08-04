<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
  'reinvoice_id',
  'material_id',
  'item_description',
  'unit',
  'quantity',
  'unit_price',
  'total_price',
])]
class ReInvoiceItem extends Model
{
  use HasFactory;

  protected $table = 're_invoice_items';

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
    static::saving(function ($reInvoiceItem) {
      if (isset($reInvoiceItem->quantity) && isset($reInvoiceItem->unit_price)) {
        $reInvoiceItem->total_price = $reInvoiceItem->quantity * $reInvoiceItem->unit_price;
      }
    });
  }

  public function reInvoice(): BelongsTo
  {
    return $this->belongsTo(ReInvoice::class, 'reinvoice_id');
  }

  public function material(): BelongsTo
  {
    return $this->belongsTo(Material::class);
  }
}
