<?php
// database/migrations/2025_09_14_000017_create_reviews_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('reviews', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
      $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->unsignedTinyInteger('rating'); // 1..5
      $table->string('title')->nullable();
      $table->text('body')->nullable();
      $table->boolean('approved')->default(false);
      $table->timestamps();
      $table->index(['product_id', 'approved', 'rating']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('reviews');
  }
};
