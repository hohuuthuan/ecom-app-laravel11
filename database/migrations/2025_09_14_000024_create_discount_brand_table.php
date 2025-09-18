<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('discount_brand', function (Blueprint $table) {
      $table->foreignUuid('discount_id')->constrained('discounts')->cascadeOnDelete();
      $table->foreignUuid('brand_id')->constrained('brands')->cascadeOnDelete();
      $table->primary(['discount_id', 'brand_id']);
    });
  }


  public function down(): void
  {
    Schema::dropIfExists('discount_brand');
  }
};
