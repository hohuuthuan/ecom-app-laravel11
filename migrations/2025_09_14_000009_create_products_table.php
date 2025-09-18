<?php
// database/migrations/2025_09_14_000009_create_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('products', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('title');
      $table->string('slug')->unique();
      $table->string('isbn')->unique();
      $table->foreignUuid('publisher_id')->nullable()->constrained('publishers')->nullOnDelete();
      $table->foreignUuid('brand_id')->nullable()->constrained('brands')->nullOnDelete();
      $table->string('sku')->nullable()->unique();
      $table->unsignedInteger('stock')->default(0);
      $table->decimal('price', 12, 2);
      $table->decimal('compare_at_price', 12, 2)->nullable();
      $table->string('status', 32)->default('ACTIVE');
      $table->date('published_at')->nullable();
      $table->longText('description')->nullable();
      $table->timestamps();
      $table->index(['status']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
