<?php
// database/migrations/2025_09_14_000012_create_author_product_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('author_product', function (Blueprint $table) {
      $table->foreignUuid('author_id')->constrained('authors')->cascadeOnDelete();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->primary(['author_id', 'product_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('author_product');
  }
};
