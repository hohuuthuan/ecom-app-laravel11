<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('orders', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('code')->unique();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

      $table->string('status', 32)->index();           // pending|confirmed|picking|shipped|delivered|cancelled|returned
      $table->string('payment_method', 32)->index();   // cod|vnpay|...
      $table->string('payment_status', 32)->index();   // unpaid|paid|...

      $table->unsignedInteger('items_count')->default(0);

      $table->unsignedBigInteger('subtotal_vnd')->default(0);
      $table->unsignedBigInteger('discount_vnd')->default(0);
      $table->unsignedBigInteger('tax_vnd')->default(0);
      $table->unsignedBigInteger('shipping_fee_vnd')->default(0);
      $table->unsignedBigInteger('grand_total_vnd')->default(0);

      $table->foreignUuid('discount_id')->nullable()->constrained('discounts')->nullOnDelete();

      $table->text('buyer_note')->nullable();

      $table->timestamp('placed_at')->index();
      $table->timestamp('delivered_at')->nullable();
      $table->timestamp('cancelled_at')->nullable();

      $table->timestamps();
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('orders');
  }
};
