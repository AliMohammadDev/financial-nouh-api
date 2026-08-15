<?php

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
    Schema::create('company_funds', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->string('status')->default('pending');
      $table->decimal('threshold', 15, 2)->nullable()->default(0);
      $table->text('description')->nullable();
      $table->string('type');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('company_funds');
  }
};
