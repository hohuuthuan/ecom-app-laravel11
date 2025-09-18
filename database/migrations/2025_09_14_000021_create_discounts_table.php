<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('discounts', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('code')->unique(); // Mã nhập ở checkout
      $table->string('name'); // Tiêu đề nội bộ/hiển thị
      $table->text('description')->nullable();
      $table->string('type', 16)->default('PERCENT'); // PERCENT | FIXED
      $table->decimal('value', 12, 2); // % (0..100) hoặc số tiền
      $table->decimal('max_discount_amount', 12, 2)->nullable();
      $table->decimal('min_order_amount', 12, 2)->nullable();
      $table->string('apply_to', 16)->default('CART'); // CART | PRODUCT | CATEGORY | BRAND | ALL
      $table->boolean('stackable')->default(false); // Có cho phép cộng dồn không
      $table->unsignedInteger('usage_limit')->nullable(); // Tổng số lần tối đa
      $table->unsignedInteger('per_user_limit')->nullable(); // Mỗi user tối đa
      $table->unsignedInteger('times_used')->default(0);
      $table->timestamp('starts_at')->nullable();
      $table->timestamp('ends_at')->nullable();
      $table->string('status', 32)->default('ACTIVE');
      $table->timestamps();
      $table->index(['status']);
      $table->index(['starts_at', 'ends_at']);
    });
  }


  public function down(): void
  {
    Schema::dropIfExists('discounts');
  }
};
