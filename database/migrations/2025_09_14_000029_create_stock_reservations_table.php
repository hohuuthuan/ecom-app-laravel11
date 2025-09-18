<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('stock_reservations', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
      $table->foreignUuid('order_item_id')->constrained('order_items')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $table->unsignedInteger('quantity');
      $table->string('status', 16)->default('PENDING'); // PENDING|RELEASED|FULFILLED
      $table->timestamp('expires_at')->nullable();
      $table->timestamps();
      $table->unique(['order_item_id']); // 1 item chỉ có 1 reservation
      $table->index(['product_id', 'warehouse_id', 'status']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('stock_reservations');
  }
};
