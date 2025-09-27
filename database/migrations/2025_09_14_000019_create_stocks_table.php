<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('stocks', function (Blueprint $table) {
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $table->bigInteger('on_hand')->default(0);
      $table->bigInteger('reserved')->default(0);
      $table->unsignedInteger('reorder_point')->nullable();
      $table->unsignedInteger('reorder_qty')->nullable();
      $table->timestamps();

      $table->primary(['product_id', 'warehouse_id']);
      $table->index(['warehouse_id', 'product_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('stocks');
  }
};
