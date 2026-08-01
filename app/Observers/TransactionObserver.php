<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Models\User;
use App\Notifications\FundBalanceAlertNotification;

class TransactionObserver
{
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
   * Handle the Transaction "updated" event.
   */
  public function updated(Transaction $transaction): void
  {
    // التحقق مما إذا تم تغير القيمة أو أحد الصناديق (سواء المصدر أو الوجهة)
    if (
      $transaction->wasChanged('amount') ||
      $transaction->wasChanged('morph_from_id') ||
      $transaction->wasChanged('morph_from_type') ||
      $transaction->wasChanged('morph_to_id') ||
      $transaction->wasChanged('morph_to_type')
    ) {

      // الخطوة أ: إعادة الوضع القديم كما كان قبل التعديل (عكس العملية القديمة)
      $oldFromType   = $transaction->getOriginal('morph_from_type');
      $oldFromId     = $transaction->getOriginal('morph_from_id');
      $oldToType     = $transaction->getOriginal('morph_to_type');
      $oldToId       = $transaction->getOriginal('morph_to_id');
      $oldAmount     = $transaction->getOriginal('amount');

      // إرجاع المبلغ للصندوق القديم الذي تم الخصم منه
      if ($oldFromType && $oldFromId) {
        $oldFromFund = $oldFromType::find($oldFromId);
        if ($oldFromFund && isset($oldFromFund->balance)) {
          $oldFromFund->increment('balance', $oldAmount);
        }
      }

      // خصم المبلغ من الصندوق القديم الذي أُضيف إليه
      if ($oldToType && $oldToId) {
        $oldToFund = $oldToType::find($oldToId);
        if ($oldToFund && isset($oldToFund->balance)) {
          $oldToFund->decrement('balance', $oldAmount);
        }
      }

      // الخطوة ب: تطبيق التعديلات الجديدة (الخصم والإضافة بناءً على القيم الجديدة)
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
    // عند حذف المعاملة بالكامل، نعكس الأرصدة لعكس ما تم أثناء الإنشاء

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


  protected function checkAndNotify($fund): void
  {
    if ($fund->balance <= 100) {
      $admins = User::whereHas('admin')->get();

      foreach ($admins as $admin) {
        $admin->notify(new FundBalanceAlertNotification($fund));
      }
    }
  }
}
