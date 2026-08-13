<?php

namespace App\Providers;

use App\Models\EmployeePayment;
use App\Models\Expense;
use App\Models\MoneyExchange;
use App\Models\Project;
use App\Models\ReInvoice;
use App\Models\Revenue;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\EmployeePaymentObserver;
use App\Observers\ExpenseObserver;
use App\Observers\MoneyExchangeObserver;
use App\Observers\ProjectObserver;
use App\Observers\ReInvoiceObserver;
use App\Observers\RevenueObserver;
use App\Observers\TransactionObserver;
use App\Observers\UserObserver;
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
    User::observe(UserObserver::class);
    Project::observe(ProjectObserver::class);
    MoneyExchange::observe(MoneyExchangeObserver::class);
  }
}
