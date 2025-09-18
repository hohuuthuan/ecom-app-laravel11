<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('discount_category', function (Blueprint $table) {
      $table->foreignUuid('discount_id')->constrained('discounts')->cascadeOnDelete();
      $table->foreignUuid('category_id')->constrained('categories')->cascadeOnDelete();
      $table->primary(['discount_id', 'category_id']);
    });
  }


  public function down(): void
  {
    Schema::dropIfExists('discount_category');
  }
};
