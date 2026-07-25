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
    Schema::create('directories', function (Blueprint $table) {
      $table->id();
      $table->string('dir_name');
      $table->string('dir_path')->nullable();

      $table->foreignId('parent_dir_id')
        ->nullable()
        ->constrained('directories')
        ->cascadeOnDelete();

      $table->foreignIdFor(Project::class)
        ->nullable()
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
    Schema::dropIfExists('directories');
  }
};
