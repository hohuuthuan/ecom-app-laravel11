<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('order_detail', function (Blueprint $table) {
      $table->uuid('id')->primary();

      // order_code FK -> orders.order_code (unique)
      $table->string('order_code', 20);
      $table->foreign('order_code')->references('order_code')->on('orders')->cascadeOnDelete();

      $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
      $table->integer('quantity')->default(1);
      $table->decimal('selling_price', 15, 2)->default(0);
      $table->decimal('subtotal', 15, 2)->default(0);
      $table->decimal('discount_amount', 15, 2)->default(0);
      $table->timestamps();

      $table->index(['order_code','product_id']);
    });
  }
  public function down() { Schema::dropIfExists('order_detail'); }
};
