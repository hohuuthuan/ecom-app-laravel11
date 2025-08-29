<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('shipping', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $table->string('name');
      $table->string('phone');
      $table->string('address');
      $table->string('email')->nullable();
      $table->string('checkout_method')->default('COD'); // COD | VNPAY
      $table->timestamps();
    });
  }
  public function down() { Schema::dropIfExists('shipping'); }
};
