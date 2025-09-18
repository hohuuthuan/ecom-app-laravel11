<?php
// database/migrations/2025_09_14_000011_create_product_category_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('product_category', function (Blueprint $table) {
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
      $table->primary(['product_id', 'category_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('product_category');
  }
};
