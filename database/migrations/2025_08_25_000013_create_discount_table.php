<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('discount', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('coupon_code')->unique();
      $table->string('discount_type'); // PERCENT | FIXED
      $table->decimal('discount_value', 15, 2)->default(0);
      $table->decimal('max_discount_value', 15, 2)->nullable();
      $table->decimal('min_order_value', 15, 2)->nullable();
      $table->decimal('max_order_value', 15, 2)->nullable();
      $table->dateTime('start_date')->nullable();
      $table->dateTime('end_date')->nullable();
      $table->string('status')->default('UNUSED'); // UNUSED | USED | EXPIRED
      $table->timestamps();
    });
  }
  public function down() { Schema::dropIfExists('discount'); }
};

