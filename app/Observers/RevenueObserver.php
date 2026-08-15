<?php

namespace App\Observers;

use App\Models\Revenue;

class RevenueObserver
{


  /**
   * Handle the Revenue "creating" event.
   */
  public function creating(Revenue $revenue): bool|null
  {
    $fund = $revenue->revenueable;

    if (empty($expense->voucher_number)) {
      $yearMonth = now()->format('y-m');

      $startOfMonth = now()->startOfMonth();
      $endOfMonth = now()->endOfMonth();

      $lastExpense = Revenue::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->orderBy('id', 'desc')
        ->first();

      $sequence = 1;
      if ($lastExpense && preg_match('/-(\d+)$/', $lastExpense->voucher_number, $matches)) {
        $sequence = intval($matches[1]) + 1;
      }

      $revenue->voucher_number = $yearMonth . '-exp-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
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
