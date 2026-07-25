<?php

use App\Models\ProjectStage;
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
    Schema::create('stage_timelines', function (Blueprint $table) {
      $table->id();

      $table->string('stage_name');
      $table->date('start_date');
      $table->date('expected_end_date');
      $table->date('actual_end_date')->nullable();
      $table->string('status');
      $table->integer('stage_progress')->default(0);

      $table->foreignIdFOr(ProjectStage::class)
        ->constrained()
        ->cascadeOnDelete();


      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('stage_timelines');
  }
};
