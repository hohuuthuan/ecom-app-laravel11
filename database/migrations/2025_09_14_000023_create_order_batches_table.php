<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('order_batches', function (Blueprint $table) {
      $table->foreignUuid('order_item_id')->constrained('order_items')->cascadeOnDelete();
      $table->foreignUuid('batch_id')->constrained('batches')->cascadeOnDelete();
      $table->unsignedInteger('quantity');
      $table->unsignedBigInteger('unit_cost_vnd');

      $table->primary(['order_item_id', 'batch_id']);
      $table->index(['batch_id']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('order_batches');
  }
};
