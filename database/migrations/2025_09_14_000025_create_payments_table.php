<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('payments', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
      $table->string('method', 32);      // cod|vnpay|...
      $table->string('provider')->nullable();
      $table->string('txn_id')->nullable();
      $table->unsignedBigInteger('amount_vnd');
      $table->unsignedBigInteger('fee_amount_vnd')->nullable();
      $table->string('status', 32)->index(); // initiated|successful|failed|refunded|partial
      $table->timestamp('paid_at')->nullable();
      $table->json('raw_gateway_payload')->nullable();
      $table->timestamps();

      $table->index(['order_id', 'paid_at']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('payments');
  }
};
