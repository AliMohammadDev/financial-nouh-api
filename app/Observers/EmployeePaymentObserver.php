<?php

namespace App\Observers;

use App\Models\CompanyFundCurrency;
use App\Models\EmployeePayment;

class EmployeePaymentObserver
{

  private function calculateTotalAmount(EmployeePayment $payment): float
  {
    $amount = (float) $payment->amount;
    $bonuses = (float) $payment->bonuses;
    $deductions = (float) $payment->deductions;

    return $amount + $bonuses - $deductions;
  }

  /**
   * Handle the EmployeePayment "created" event.
   */
  public function created(EmployeePayment $employeePayment): void
  {
    if ($employeePayment->companyFundCurrency) {
      $totalAmount = $this->calculateTotalAmount($employeePayment);
      $employeePayment->companyFundCurrency->decrement('balance', $totalAmount);
    }
  }

  /**
   * Handle the EmployeePayment "updated" event.
   */
  public function updated(EmployeePayment $employeePayment): void
  {
    $oldAmount = (float) $employeePayment->getOriginal('amount');
    $oldBonuses = (float) $employeePayment->getOriginal('bonuses');
    $oldDeductions = (float) $employeePayment->getOriginal('deductions');
    $oldTotal = $oldAmount + $oldBonuses - $oldDeductions;

    $oldFundCurrencyId = $employeePayment->getOriginal('company_fund_currency_id');

    $newTotal = $this->calculateTotalAmount($employeePayment);
    $newFundCurrencyId = $employeePayment->company_fund_currency_id;

    if ($oldFundCurrencyId == $newFundCurrencyId) {
      $difference = $newTotal - $oldTotal;
      if ($difference != 0) {
        $employeePayment->companyFundCurrency()->decrement('balance', $difference);
      }
    } else {
      CompanyFundCurrency::where('id', $oldFundCurrencyId)->increment('balance', $oldTotal);

      if ($employeePayment->companyFundCurrency) {
        $employeePayment->companyFundCurrency->decrement('balance', $newTotal);
      }
    }
  }

  /**
   * Handle the EmployeePayment "deleted" event.
   */
  public function deleted(EmployeePayment $employeePayment): void
  {
    if ($employeePayment->companyFundCurrency) {
      $totalAmount = $this->calculateTotalAmount($employeePayment);
      $employeePayment->companyFundCurrency->increment('balance', $totalAmount);
    }
  }

  public function restored(EmployeePayment $employeePayment): void
  {
    //
  }

  public function forceDeleted(EmployeePayment $employeePayment): void
  {
    //
  }
}
