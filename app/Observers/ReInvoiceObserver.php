<?php

namespace App\Observers;

use App\Models\ReInvoice;

class ReInvoiceObserver
{
  /**
   * Handle the ReInvoice "created" event.
   */
  public function created(ReInvoice $reInvoice): void
  {
    $fundCurrency = $reInvoice->reinvoiceable;

    if ($fundCurrency && isset($fundCurrency->balance)) {
      $fundCurrency->increment('balance', $reInvoice->final_total);
    }
  }

  /**
   * Handle the ReInvoice "updated" event.
   */
  public function updated(ReInvoice $reInvoice): void
  {
    if ($reInvoice->wasChanged('final_total') || $reInvoice->wasChanged('reinvoiceable_id') || $reInvoice->wasChanged('reinvoiceable_type')) {

      $originalType = $reInvoice->getOriginal('reinvoiceable_type');
      $originalId   = $reInvoice->getOriginal('reinvoiceable_id');
      $oldAmount    = $reInvoice->getOriginal('final_total');

      $oldFundCurrency = $originalType::find($originalId);
      if ($oldFundCurrency && isset($oldFundCurrency->balance)) {
        $oldFundCurrency->decrement('balance', $oldAmount);
      }

      $newFundCurrency = $reInvoice->reinvoiceable;
      if ($newFundCurrency && isset($newFundCurrency->balance)) {
        $newFundCurrency->increment('balance', $reInvoice->final_total);
      }
    }
  }

  /**
   * Handle the ReInvoice "deleted" event.
   */
  public function deleted(ReInvoice $reInvoice): void
  {
    $fundCurrency = $reInvoice->reinvoiceable;

    if ($fundCurrency && isset($fundCurrency->balance)) {
      $fundCurrency->decrement('balance', $reInvoice->final_total);
    }
  }

  /**
   * Handle the ReInvoice "restored" event.
   */
  public function restored(ReInvoice $reInvoice): void
  {
    //
  }

  /**
   * Handle the ReInvoice "force deleted" event.
   */
  public function forceDeleted(ReInvoice $reInvoice): void
  {
    //
  }
}
