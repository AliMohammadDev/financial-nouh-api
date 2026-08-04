<?php

use App\Models\Invoice;
use App\Models\Material;
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
    Schema::create('invoice_items', function (Blueprint $table) {
      $table->id();

      $table->foreignIdFor(Invoice::class)
        ->constrained()
        ->cascadeOnDelete();

      $table->foreignIdFor(Material::class)
        ->constrained();

      $table->text('item_description');

      $table->string('unit');
      $table->decimal('quantity', 15, 2);
      $table->decimal('unit_price', 15, 2);
      $table->decimal('total_price', 15, 2);

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('invoice_items');
  }
};
