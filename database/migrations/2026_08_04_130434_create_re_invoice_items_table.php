<?php

use App\Models\Material;
use App\Models\ReInvoice;
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
    Schema::create('re_invoice_items', function (Blueprint $table) {
      $table->id();

      $table->foreignIdFor(ReInvoice::class, 'reinvoice_id')
        ->constrained('re_invoices')
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
    Schema::dropIfExists('re_invoice_items');
  }
};
