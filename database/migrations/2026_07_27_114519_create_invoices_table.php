<?php

use App\Models\Expense;
use App\Models\Item;
use App\Models\Supplier;
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
    Schema::create('invoices', function (Blueprint $table) {
      $table->id();

      $table->foreignIdFor(Item::class)
        ->constrained();

      $table->foreignIdFor(Expense::class)
        ->constrained();

      $table->foreignIdFor(Supplier::class)
        ->nullable()
        ->constrained();

      $table->string('invoice_number')->unique();
      $table->timestamp('date')->nullable();
      $table->decimal('discount', 10, 2)->default(0);
      $table->decimal('final_total', 10, 2)->default(0);
      $table->boolean('is_posted')->default(true);
      $table->boolean('is_visible_to_client')->default(true);

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('invoices');
  }
};
