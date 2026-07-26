<?php

use App\Models\Employee;
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
    Schema::create('expenses', function (Blueprint $table) {
      $table->id();
      $table->morphs('expenseable');
      $table->text('description');
      $table->decimal('amount', 15, 2);
      $table->boolean('is_posted')->default(false);
      $table->foreignIdFor(User::class)
        ->constrained();

      $table->foreignId('created_by')
        ->constrained('users');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('expenses');
  }
};
