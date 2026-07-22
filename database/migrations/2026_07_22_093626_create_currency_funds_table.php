<?php

use App\Models\Currency;
use App\Models\Fund;
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
    Schema::create('currency_funds', function (Blueprint $table) {
      $table->id();
      $table->foreignIdFor(Currency::class)
        ->constrained()
        ->cascadeOnDelete();
      $table->foreignIdFor(Fund::class)
        ->constrained()
        ->cascadeOnDelete();
      $table->decimal('balance', 15, 2)
        ->default(0.00);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('currency_funds');
  }
};
