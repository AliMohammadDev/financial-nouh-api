<?php

namespace App\Observers;

use App\Models\Expense;
use App\Models\User;
use App\Notifications\FundBalanceAlertNotification;

class ExpenseObserver
{


  /**
   * Handle the Expense "creating" event.
   */
  public function creating(Expense $expense): bool|null
  {
    $fund = $expense->expenseable;

    if (empty($expense->voucher_number)) {
      $yearMonth = now()->format('y-m');

      $startOfMonth = now()->startOfMonth();
      $endOfMonth = now()->endOfMonth();

      $lastExpense = Expense::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->orderBy('id', 'desc')
        ->first();

      $sequence = 1;
      if ($lastExpense && preg_match('/-(\d+)$/', $lastExpense->voucher_number, $matches)) {
        $sequence = intval($matches[1]) + 1;
      }

      $expense->voucher_number = $yearMonth . '-exp-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }

    return true;
  }
  /**
   * Handle the Expense "created" event.
   */
  public function created(Expense $expense): void
  {
    $fund = $expense->expenseable;

    if ($fund && isset($fund->balance)) {
      $fund->decrement('balance', $expense->amount);
      $this->checkAndNotify($fund);
    }
  }

  /**
   * Handle the Expense "updating" event.
   */
  public function updating(Expense $expense): bool|null
  {
    $fund = $expense->expenseable;

    return true;
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
        $this->checkAndNotify($newFund);
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
