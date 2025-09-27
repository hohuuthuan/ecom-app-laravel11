<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('batch_stocks', function (Blueprint $table) {
      $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $table->bigInteger('on_hand')->default(0);
      $table->bigInteger('reserved')->default(0);
      $table->timestamps();

      $table->primary(['batch_id']);
      $table->index(['product_id', 'warehouse_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('batch_stocks');
  }
};
