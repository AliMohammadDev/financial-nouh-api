<?php

namespace App\Observers;

use App\Models\CompanyFundCurrency;
use App\Models\EmployeePayment;

class EmployeePaymentObserver
{

  protected function isFundLocked($fundCurrency): bool
  {
    if (!$fundCurrency) {
      return false;
    }

    $mainFund = method_exists($fundCurrency, 'companyFund') ? $fundCurrency->companyFund : null;

    return $mainFund && isset($mainFund->is_locked) && $mainFund->is_locked;
  }

  private function calculateTotalAmount(EmployeePayment $payment): float
  {
    $amount = (float) $payment->amount;
    $bonuses = (float) $payment->bonuses;
    $deductions = (float) $payment->deductions;

    return $amount + $bonuses - $deductions;
  }

  /**
   * Handle the EmployeePayment "creating" event.
   */
  public function creating(EmployeePayment $employeePayment): bool|null
  {
    $fundCurrency = $employeePayment->companyFundCurrency;

    if ($this->isFundLocked($fundCurrency)) {
      throw new \Exception('لا يمكن إضافة دفعة للموظف، لأن صندوق الصرف مقفل حالياً.');
    }

    return true;
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
   * Handle the EmployeePayment "updating" event.
   */
  public function updating(EmployeePayment $employeePayment): bool|null
  {
    $fundCurrency = $employeePayment->companyFundCurrency;

    if ($this->isFundLocked($fundCurrency)) {
      throw new \Exception('لا يمكن تعديل دفعة الموظف، لأن صندوق الصرف مقفل حالياً.');
    }

    return true;
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
