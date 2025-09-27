<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('product_authors', function (Blueprint $table) {
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('author_id')->constrained('authors')->cascadeOnDelete();
      $table->string('role')->nullable();
      $table->primary(['product_id', 'author_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('product_authors');
  }
};
