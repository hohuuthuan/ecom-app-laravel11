<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('order_items', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->string('product_title_snapshot');
      $table->string('isbn13_snapshot')->nullable();
      $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();

      $table->unsignedInteger('quantity');
      $table->unsignedBigInteger('unit_price_vnd');
      $table->unsignedBigInteger('discount_amount_vnd')->default(0);
      $table->decimal('tax_rate', 5, 2)->nullable();
      $table->unsignedBigInteger('tax_amount_vnd')->default(0);
      $table->unsignedBigInteger('unit_cost_snapshot_vnd'); // giá vốn snapshotted
      $table->unsignedBigInteger('total_price_vnd')->default(0);

      $table->timestamps();

      $table->index(['order_id']);
      $table->index(['product_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('order_items');
  }
};
