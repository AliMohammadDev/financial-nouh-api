<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
  'item_id',
  'supplier_id',
  'reinvoiceable_type',
  'reinvoiceable_id',
  'reinvoice_number',
  'date',
  'discount',
  'final_total',
  'is_posted',
  'is_visible_to_client',
])]
class ReInvoice extends Model
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

  public function supplier(): BelongsTo
  {
    return $this->belongsTo(User::class, 'supplier_id');
  }

  public function reinvoiceable(): MorphTo
  {
    return $this->morphTo();
  }
}
