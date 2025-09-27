<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('batches', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('purchase_receipt_item_id')->constrained('purchase_receipt_items')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $table->unsignedInteger('quantity');
      $table->unsignedBigInteger('import_price_vnd');
      $table->date('import_date');
      $table->timestamps();

      $table->index(['product_id', 'warehouse_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('batches');
  }
};
