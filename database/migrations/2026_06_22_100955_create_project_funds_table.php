<?php

use App\Models\Client;
use App\Models\Fund;
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
    Schema::create('project_funds', function (Blueprint $table) {
      $table->id();
      $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
      $table->string('name');
      $table->string('type');
      $table->string('currency')->default('USD');
      $table->decimal('balance_usd', 15, 2)->default(0);
      $table->decimal('balance_syp', 15, 2)->default(0);
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('project_funds');
  }
};
