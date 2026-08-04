<?php

namespace App\Providers;

use App\Models\EmployeePayment;
use App\Models\Expense;
use App\Models\ReInvoice;
use App\Models\Revenue;
use App\Models\Transaction;
use App\Observers\EmployeePaymentObserver;
use App\Observers\ExpenseObserver;
use App\Observers\ReInvoiceObserver;
use App\Observers\RevenueObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {
    Expense::observe(ExpenseObserver::class);
    EmployeePayment::observe(EmployeePaymentObserver::class);
    Revenue::observe(RevenueObserver::class);
    Transaction::observe(TransactionObserver::class);
    ReInvoice::observe(ReInvoiceObserver::class);
  }
}
