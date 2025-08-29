<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('products', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('product_code')->unique();
      $table->string('name');
      $table->text('description')->nullable();
      $table->string('unit')->nullable(); // vd: box, bottle...
      $table->decimal('selling_price', 15, 2)->default(0);
      $table->decimal('sale', 5, 2)->default(0); // % giảm giá
      $table->string('image')->nullable();
      $table->string('slug')->unique();
      $table->foreignUuid('category_id')->constrained('categories')->restrictOnDelete();
      $table->foreignUuid('brand_id')->constrained('brands')->restrictOnDelete();
      $table->string('status')->default('ACTIVE');
      $table->timestamps();
    });
  }
  public function down() { Schema::dropIfExists('products'); }
};
