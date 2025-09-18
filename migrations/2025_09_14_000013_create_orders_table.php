<?php
// database/migrations/2025_09_14_000013_create_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('orders', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->string('code')->unique();
      $table->string('status', 32)->default('PENDING');
      $table->decimal('subtotal_amount', 12, 2)->default(0);
      $table->decimal('discount_amount', 12, 2)->default(0);
      $table->decimal('shipping_amount', 12, 2)->default(0);
      $table->decimal('total_amount', 12, 2)->default(0);
      $table->json('shipping_address')->nullable();
      $table->text('note')->nullable();
      $table->timestamp('placed_at')->nullable();
      $table->timestamps();
      $table->index(['status', 'placed_at']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('orders');
  }
};
