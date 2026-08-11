<?php

namespace App\Observers;

use App\Models\Revenue;

class RevenueObserver
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
   * Handle the Revenue "creating" event.
   */
  public function creating(Revenue $revenue): bool|null
  {
    $fund = $revenue->revenueable;

    if ($this->isFundLocked($fund)) {
      throw new \Exception('لا يمكن إضافة إيراد، لأن الصندوق مقفل حالياً.');
    }

    return true;
  }
  /**
   * Handle the Revenue "created" event.
   */
  public function created(Revenue $revenue): void
  {
    $fund = $revenue->revenueable;

    if ($fund && isset($fund->balance)) {
      $fund->increment('balance', $revenue->amount);
    }
  }

  /**
   * Handle the Revenue "updating" event.
   */
  public function updating(Revenue $revenue): bool|null
  {
    $fund = $revenue->revenueable;

    if ($this->isFundLocked($fund)) {
      throw new \Exception('لا يمكن تعديل الإيراد، لأن الصندوق مقفل حالياً.');
    }

    return true;
  }
  /**
   * Handle the Revenue "updated" event.
   */
  public function updated(Revenue $revenue): void
  {
    if ($revenue->wasChanged('amount') || $revenue->wasChanged('revenueable_id') || $revenue->wasChanged('revenueable_type')) {

      $originalType = $revenue->getOriginal('revenueable_type');
      $originalId   = $revenue->getOriginal('revenueable_id');
      $oldAmount    = $revenue->getOriginal('amount');

      $oldFund = $originalType::find($originalId);
      if ($oldFund && isset($oldFund->balance)) {
        $oldFund->decrement('balance', $oldAmount);
      }

      $newFund = $revenue->revenueable;
      if ($newFund && isset($newFund->balance)) {
        $newFund->increment('balance', $revenue->amount);
      }
    }
  }

  /**
   * Handle the Revenue "deleted" event.
   */
  public function deleted(Revenue $revenue): void
  {
    $fund = $revenue->revenueable;

    if ($fund && isset($fund->balance)) {
      $fund->decrement('balance', $revenue->amount);
    }
  }

  /**
   * Handle the Revenue "restored" event.
   */
  public function restored(Revenue $revenue): void
  {
    //
  }

  /**
   * Handle the Revenue "force deleted" event.
   */
  public function forceDeleted(Revenue $revenue): void
  {
    //
  }
}
