<?php

use App\Models\CompanyFundCurrency;
use App\Models\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('employee_payments', function (Blueprint $table) {
      $table->id();
      $table->foreignIdFor(Employee::class)
        ->constrained();

      $table->foreignIdFor(CompanyFundCurrency::class, 'company_fund_currency_id')
        ->constrained('company_fund_currencies')
        ->cascadeOnDelete();

      $table->decimal('bonuses', 10, 2)->default(0);
      $table->decimal('deductions', 10, 2)->default(0);
      $table->date('payment_date');
      $table->decimal('amount', 10, 2);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('employee_payments');
  }
};
