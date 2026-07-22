<?php

use App\Models\Currency;
use App\Models\ProjectFund;
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
    Schema::create('project_fund_currencies', function (Blueprint $table) {
      $table->id();
      $table->foreignIdFor(ProjectFund::class)->constrained()->cascadeOnDelete();
      $table->foreignIdFor(Currency::class)->constrained()->cascadeOnDelete();
      $table->decimal('balance', 15, 2)->default(0.00);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_fund_currencies');
  }
};
