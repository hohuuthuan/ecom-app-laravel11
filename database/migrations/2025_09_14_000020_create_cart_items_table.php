<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('cart_items', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('cart_id')->constrained('carts')->cascadeOnDelete();
      $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
      $table->unsignedInteger('quantity')->default(1);
      $table->decimal('unit_price', 12, 2)->default(0);
      $table->timestamps();
      $table->unique(['cart_id', 'product_id']);
      $table->index(['cart_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('cart_items');
  }
};
