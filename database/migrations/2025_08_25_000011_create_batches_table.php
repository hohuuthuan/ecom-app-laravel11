<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('batches', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('warehouse_receipt_id')->constrained('warehouse_receipt')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
      $table->integer('quantity')->default(0);
      $table->date('import_date')->nullable();
      $table->date('expiry_date')->nullable();
      $table->decimal('import_price', 15, 2)->default(0);
      $table->foreignUuid('supplier_id')->constrained('suppliers')->restrictOnDelete();
      $table->integer('remaining_quantity')->default(0);
      $table->timestamps();
      $table->index(['product_id','supplier_id']);
    });
  }
  public function down() { Schema::dropIfExists('batches'); }
};

