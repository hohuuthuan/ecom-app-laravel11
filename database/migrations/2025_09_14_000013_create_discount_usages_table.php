<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('discount_usages', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('discount_id')->constrained('discounts')->cascadeOnDelete();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      // order_id sẽ được khóa ngoại khi bảng orders tạo sau
      $table->uuid('order_id')->nullable()->index();
      $table->timestamp('used_at')->nullable();
      $table->timestamps();

      $table->index(['discount_id', 'user_id']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('discount_usages');
  }
};
