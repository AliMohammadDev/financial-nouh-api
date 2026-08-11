<?php

namespace App\Observers;

use App\Models\ReInvoice;

class ReInvoiceObserver
{

  protected function isFundLocked($fundCurrency): bool
  {
    if (!$fundCurrency) {
      return false;
    }

    $mainFund = null;

    if (method_exists($fundCurrency, 'companyFund')) {
      $mainFund = $fundCurrency->companyFund;
    } elseif (method_exists($fundCurrency, 'projectFund')) {
      $mainFund = $fundCurrency->projectFund;
    } elseif (method_exists($fundCurrency, 'fund')) {
      $mainFund = $fundCurrency->fund;
    }

    return $mainFund && isset($mainFund->is_locked) && $mainFund->is_locked;
  }

  /**
   * Handle the ReInvoice "creating" event.
   */
  public function creating(ReInvoice $reInvoice): bool|null
  {
    $fundCurrency = $reInvoice->reinvoiceable;

    if ($this->isFundLocked($fundCurrency)) {
      throw new \Exception('لا يمكن إضافة الفاتورة، لأن الصندوق مقفل حالياً.');
    }

    return true;
  }
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
   * Handle the ReInvoice "updating" event.
   */
  public function updating(ReInvoice $reInvoice): bool|null
  {
    $fundCurrency = $reInvoice->reinvoiceable;

    if ($this->isFundLocked($fundCurrency)) {
      throw new \Exception('لا يمكن تعديل الفاتورة، لأن الصندوق مقفل حالياً.');
    }

    return true;
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
