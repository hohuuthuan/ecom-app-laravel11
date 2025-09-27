<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('reviews', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $table->unsignedTinyInteger('rating'); // 1..5
      $table->text('comment')->nullable();
      $table->text('reply')->nullable();
      $table->boolean('is_active')->default(true)->index();
      $table->timestamps();

      $table->index(['product_id', 'is_active']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('reviews');
  }
};
