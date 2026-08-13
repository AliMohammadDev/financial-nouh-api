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
      Schema::create('money_exchanges', function (Blueprint $table) {
        $table->id();
        $table->morphs('exchangeable');
        $table->foreignId('from_currency')->constrained('currencies');
        $table->foreignId('to_currency')->constrained('currencies');
        $table->text('amount')->nullable();
        $table->decimal('exchange_rate', 15, 2);
        $table->enum('operation', ['multiply', 'divide'])->default('multiply');
        $table->decimal('converted_amount', 15, 2)->default(0.00);
        $table->decimal('exp', 15, 4)->nullable();
        $table->text('notes')->nullable();
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
      Schema::dropIfExists('money_exchanges');
    }
  };
