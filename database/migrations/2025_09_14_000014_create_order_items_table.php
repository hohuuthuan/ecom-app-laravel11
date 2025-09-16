<?php
// database/migrations/2025_09_14_000014_create_order_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('order_items', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
      $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
      $table->string('product_title');
      $table->string('product_isbn')->nullable();
      $table->unsignedInteger('quantity');
      $table->decimal('unit_price', 12, 2);
      $table->decimal('total_price', 12, 2);
      $table->timestamps();
      $table->index(['order_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('order_items');
  }
};
