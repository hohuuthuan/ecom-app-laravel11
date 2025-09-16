<?php
// database/migrations/2025_09_14_000010_create_product_images_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('product_images', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->string('path');
      $table->unsignedSmallInteger('position')->default(0);
      $table->timestamps();
      $table->index(['product_id', 'position']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('product_images');
  }
};
