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
        $table->decimal('exchange_rate', 15, 4);
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
