<?php

use App\Models\Project;
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
    Schema::create('project_stages', function (Blueprint $table) {
      $table->id();
      $table->string('name');
      $table->date('start_date');
      $table->date('expected_end_date');
      $table->date('actual_end_date')->nullable();
      $table->string('status');
      $table->integer('stage_progress')->default(0); // نسبة الإنجاز 0-100

      $table->unique(['project_id', 'name']);

      $table->foreignIdFor(Project::class)
        ->constrained();

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_stages');
  }
};
