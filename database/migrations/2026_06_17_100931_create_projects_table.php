<?php

use App\Models\Client;
use App\Models\Department;
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
    Schema::create('projects', function (Blueprint $table) {
      $table->id();
      $table->foreignIdFor(Client::class)
        ->constrained()
        ->cascadeOnDelete();
      $table->foreignIdFor(Department::class)
        ->constrained();
      $table->string('name');
      $table->decimal('expected_cost', 15, 2);
      $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('projects');
  }
};
