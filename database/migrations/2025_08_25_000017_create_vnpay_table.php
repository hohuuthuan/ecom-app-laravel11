<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('vnpay', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('shipping_id')->constrained('shipping')->cascadeOnDelete();

      $table->string('vnp_Amount')->nullable();
      $table->string('vnp_BankCode')->nullable();
      $table->string('vnp_BankTranNo')->nullable();
      $table->string('vnp_CardType')->nullable();
      $table->string('vnp_OrderInfo')->nullable();
      $table->string('vnp_PayDate')->nullable();
      $table->string('vnp_ResponseCode')->nullable();
      $table->string('vnp_TmnCode')->nullable();
      $table->string('vnp_TransactionStatus')->nullable();
      $table->string('vnp_TxnRef')->nullable();
      $table->string('vnp_SecureHash')->nullable();

      $table->timestamps();
      $table->index(['shipping_id','vnp_TxnRef']);
    });
  }
  public function down() { Schema::dropIfExists('vnpay'); }
};

