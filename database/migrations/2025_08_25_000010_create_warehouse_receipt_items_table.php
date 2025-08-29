<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('warehouse_receipt_items', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('receipt_id')->constrained('warehouse_receipt')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
      $table->string('product_code');
      $table->string('unit')->nullable();
      $table->decimal('import_price', 15, 2)->default(0);
      $table->date('exp_date')->nullable();
      $table->integer('quantity_doc')->default(0);
      $table->integer('quantity_actual')->default(0);
      $table->text('notes')->nullable();
      $table->timestamps();
      $table->index(['receipt_id','product_id']);
    });
  }
  public function down() { Schema::dropIfExists('warehouse_receipt_items'); }
};

