<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_receipt_items', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('purchase_receipt_id')->constrained('purchase_receipts')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->unsignedBigInteger('import_price_vnd');
      $table->unsignedInteger('qty_doc');
      $table->unsignedInteger('qty_actual');
      $table->text('notes')->nullable();
      $table->timestamps();

      $table->index(['purchase_receipt_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('purchase_receipt_items');
  }
};
