<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('discount_redemptions', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('discount_id')->nullable()->constrained('discounts')->nullOnDelete();
      $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
      $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->string('code'); // snapshot, phòng trường hợp mã bị đổi/xoá
      $table->decimal('amount_applied', 12, 2); // số tiền giảm thực tế
      $table->timestamp('redeemed_at')->nullable();
      $table->timestamps();
      $table->unique(['order_id', 'discount_id']); // 1 đơn, 1 mã 1 lần
      $table->index(['user_id', 'redeemed_at']);
    });
  }


  public function down(): void
  {
    Schema::dropIfExists('discount_redemptions');
  }
};
