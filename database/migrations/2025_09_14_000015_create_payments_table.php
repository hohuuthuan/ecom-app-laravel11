<?php
// database/migrations/2025_09_14_000015_create_payments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('payments', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
      $table->string('provider'); // vd: vnpay, cod
      $table->string('status', 32)->default('PENDING');
      $table->decimal('amount', 12, 2);
      $table->string('currency', 3)->default('VND');
      $table->string('transaction_id')->nullable();
      $table->timestamp('paid_at')->nullable();
      $table->timestamps();
      $table->index(['provider', 'status']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('payments');
  }
};
