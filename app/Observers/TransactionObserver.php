<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\User;
use App\Notifications\FundBalanceAlertNotification;

class TransactionObserver
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
   * Handle the Transaction "creating" event.
   */
  public function creating(Transaction $transaction): bool|null
  {
    $fromFund = $transaction->morphFrom;
    $toFund   = $transaction->morphToFund;

    if ($this->isFundLocked($fromFund)) {
      throw new \Exception('لا يمكن إتمام التحويل، لأن صندوق المصدر مقفل حالياً.');
    }

    if ($this->isFundLocked($toFund)) {
      throw new \Exception('لا يمكن إتمام التحويل، لأن صندوق الوجهة مقفل حالياً.');
    }

    return true;
  }
  /**
   * Handle the Transaction "created" event.
   */
  public function created(Transaction $transaction): void
  {
    // 1. خصم المبلغ من صندوق المصدر (Morph From)
    $fromFund = $transaction->morphFrom;
    if ($fromFund && isset($fromFund->balance)) {
      $fromFund->decrement('balance', $transaction->amount);
      $this->checkAndNotify($fromFund);
    }

    // 2. إضافة المبلغ إلى صندوق الوجهة (Morph To)
    $toFund = $transaction->morphToFund;
    if ($toFund && isset($toFund->balance)) {
      $toFund->increment('balance', $transaction->amount);
    }
  }

  /**
   * Handle the Transaction "updating" event.
   */
  public function updating(Transaction $transaction): bool|null
  {
    $fromFund = $transaction->morphFrom;
    $toFund   = $transaction->morphToFund;

    if ($this->isFundLocked($fromFund)) {
      throw new \Exception('لا يمكن تعديل الحركة، لأن صندوق المصدر مقفل حالياً.');
    }

    if ($this->isFundLocked($toFund)) {
      throw new \Exception('لا يمكن تعديل الحركة، لأن صندوق الوجهة مقفل حالياً.');
    }

    return true;
  }

  /**
   * Handle the Transaction "updated" event.
   */
  public function updated(Transaction $transaction): void
  {
    if (
      $transaction->wasChanged('amount') ||
      $transaction->wasChanged('morph_from_id') ||
      $transaction->wasChanged('morph_from_type') ||
      $transaction->wasChanged('morph_to_id') ||
      $transaction->wasChanged('morph_to_type')
    ) {

      $oldFromType   = $transaction->getOriginal('morph_from_type');
      $oldFromId     = $transaction->getOriginal('morph_from_id');
      $oldToType     = $transaction->getOriginal('morph_to_type');
      $oldToId       = $transaction->getOriginal('morph_to_id');
      $oldAmount     = $transaction->getOriginal('amount');

      if ($oldFromType && $oldFromId) {
        $oldFromFund = $oldFromType::find($oldFromId);
        if ($oldFromFund && isset($oldFromFund->balance)) {
          $oldFromFund->increment('balance', $oldAmount);
        }
      }

      if ($oldToType && $oldToId) {
        $oldToFund = $oldToType::find($oldToId);
        if ($oldToFund && isset($oldToFund->balance)) {
          $oldToFund->decrement('balance', $oldAmount);
        }
      }

      $newFromFund = $transaction->morphFrom;
      if ($newFromFund && isset($newFromFund->balance)) {
        $newFromFund->decrement('balance', $transaction->amount);
        $this->checkAndNotify($newFromFund);
      }

      $newToFund = $transaction->morphToFund;
      if ($newToFund && isset($newToFund->balance)) {
        $newToFund->increment('balance', $transaction->amount);
      }
    }
  }

  /**
   * Handle the Transaction "deleted" event.
   */
  public function deleted(Transaction $transaction): void
  {

    // 1. إعادة المبلغ للصندوق الذي تم الخصم منه
    $fromFund = $transaction->morphFrom;
    if ($fromFund && isset($fromFund->balance)) {
      $fromFund->increment('balance', $transaction->amount);
    }

    // 2. خصم المبلغ من الصندوق الذي أُضيف إليه
    $toFund = $transaction->morphToFund;
    if ($toFund && isset($toFund->balance)) {
      $toFund->decrement('balance', $transaction->amount);
    }
  }

  /**
   * Handle the Transaction "restored" event.
   */
  public function restored(Transaction $transaction): void
  {
    //
  }

  /**
   * Handle the Transaction "force deleted" event.
   */
  public function forceDeleted(Transaction $transaction): void
  {
    //
  }


  protected function checkAndNotify($fundCurrency): void
  {
    $mainFund = null;

    if (method_exists($fundCurrency, 'companyFund')) {
      $mainFund = $fundCurrency->companyFund;
    } elseif (method_exists($fundCurrency, 'projectFund')) {
      $mainFund = $fundCurrency->projectFund;
    } elseif (method_exists($fundCurrency, 'fund')) {
      $mainFund = $fundCurrency->fund;
    }

    if ($mainFund && !is_null($mainFund->threshold) && $fundCurrency->balance <= $mainFund->threshold) {
      $admins = User::whereHas('admin')->get();

      foreach ($admins as $admin) {
        $admin->notify(new FundBalanceAlertNotification($fundCurrency));
      }
    }
  }
}
