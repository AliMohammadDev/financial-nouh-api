<?php

use App\Models\CurrencyFund;
use App\Models\Item;
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
    Schema::create('re_invoices', function (Blueprint $table) {
      $table->id();

      $table->foreignIdFor(Item::class)
        ->constrained();

      $table->foreignId('supplier_id')
        ->nullable()
        ->constrained('users');

      $table->morphs('reinvoiceable');

      $table->string('reinvoice_number')->unique();
      $table->timestamp('date')->nullable();
      $table->decimal('discount', 15, 2)->default(0);
      $table->decimal('final_total', 15, 2)->default(0);
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
    Schema::dropIfExists('re_invoices');
  }
};
