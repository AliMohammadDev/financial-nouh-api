<?php

use App\Models\User;
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
    Schema::create('revenues', function (Blueprint $table) {
      $table->id();
      $table->string('voucher_number')->unique();
      $table->morphs('revenueable');
      $table->text('statement');
      $table->decimal('amount', 15, 2);

      $table->text('note')->nullable();

      $table->foreignId('received_by')
        ->nullable()
        ->constrained('users');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('revenues');
  }
};
