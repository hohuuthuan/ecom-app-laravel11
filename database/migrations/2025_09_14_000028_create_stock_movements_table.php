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
      $table->string('type', 16); // INBOUND|OUTBOUND|ADJUST|RESERVE|RELEASE|FULFILL
      $table->integer('quantity'); // dương hoặc âm tuỳ type
      $table->nullableUuidMorphs('related'); // related_type, related_id (vd: order, receipt)
      $table->string('note')->nullable();
      $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
      $table->timestamps();
      $table->index(['product_id', 'warehouse_id', 'type', 'created_at']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('stock_movements');
  }
};
