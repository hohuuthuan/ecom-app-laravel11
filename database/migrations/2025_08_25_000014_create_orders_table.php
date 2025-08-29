<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('orders', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
      $table->foreignUuid('discount_id')->nullable()->constrained('discount')->nullOnDelete();
      $table->foreignUuid('shipping_id')->nullable()->constrained('shipping')->nullOnDelete();

      $table->string('order_code', 20)->unique(); // random 10 chars (A-Z0-9)
      $table->string('order_status')->default('PENDING'); 
      // PENDING, PROCESSING, PACKING, SHIPPING, DELIVERED(COD), PAID(VNPAY), CANCELED...

      $table->string('payment_status')->default('UNPAID'); // UNPAID | PAID
      $table->decimal('total_amount', 15, 2)->default(0);
      $table->dateTime('date_delivered')->nullable();
      $table->dateTime('payment_date_successful')->nullable();
      $table->timestamps();
    });
  }
  public function down() { Schema::dropIfExists('orders'); }
};

