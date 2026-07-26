<?php

namespace App\Observers;

use App\Models\Expense;

class ExpenseObserver
{
  /**
   * Handle the Expense "created" event.
   */
  public function created(Expense $expense): void
  {
    $fund = $expense->expenseable;

    if ($fund && isset($fund->balance)) {
      $fund->decrement('balance', $expense->amount);
    }
  }

  /**
   * Handle the Expense "updated" event.
   */
  public function updated(Expense $expense): void
  {
    if ($expense->wasChanged('amount') || $expense->wasChanged('expenseable_id') || $expense->wasChanged('expenseable_type')) {

      $originalType = $expense->getOriginal('expenseable_type');
      $originalId   = $expense->getOriginal('expenseable_id');
      $oldAmount    = $expense->getOriginal('amount');

      $oldFund = $originalType::find($originalId);
      if ($oldFund && isset($oldFund->balance)) {
        $oldFund->increment('balance', $oldAmount);
      }

      $newFund = $expense->expenseable;
      if ($newFund && isset($newFund->balance)) {
        $newFund->decrement('balance', $expense->amount);
      }
    }
  }

  /**
   * Handle the Expense "deleted" event.
   */
  public function deleted(Expense $expense): void
  {
    $fund = $expense->expenseable;

    if ($fund && isset($fund->balance)) {
      $fund->increment('balance', $expense->amount);
    }
  }

  /**
   * Handle the Expense "restored" event.
   */
  public function restored(Expense $expense): void
  {
    //
  }

  /**
   * Handle the Expense "force deleted" event.
   */
  public function forceDeleted(Expense $expense): void
  {
    //
  }
}
