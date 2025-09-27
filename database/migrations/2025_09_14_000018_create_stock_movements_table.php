<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('stock_movements', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $table->foreignUuid('batch_id')->nullable()->constrained('batches')->nullOnDelete();
      $table->string('type', 32); // receipt|sale|adjustment|transfer_in|transfer_out
      $table->integer('qty');     // >0 nhập, <0 xuất
      $table->unsignedBigInteger('unit_cost_vnd')->default(0);
      $table->string('related_type')->nullable();
      $table->uuid('related_id')->nullable()->index();
      $table->text('note')->nullable();
      $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
      $table->timestamps();

      $table->index(['product_id', 'warehouse_id', 'created_at']);
      $table->index(['batch_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('stock_movements');
  }
};
