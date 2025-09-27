<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('products', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('code')->unique();
      $table->string('title');
      $table->string('slug')->unique();
      $table->string('isbn')->nullable()->unique();
      $table->text('description')->nullable();
      $table->foreignUuid('publisher_id')->nullable()
        ->constrained('publishers')->nullOnDelete();
      $table->string('unit', 32)->default('book');
      $table->unsignedBigInteger('selling_price_vnd');
      $table->string('image')->nullable();
      $table->string('status', 32)->default('ACTIVE');
      $table->timestamps();

      $table->index(['publisher_id', 'status']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('products');
  }
};
