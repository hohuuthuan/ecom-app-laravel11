<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('shipments', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
      $table->string('name');
      $table->string('phone', 32);
      $table->string('email')->nullable();
      $table->text('address');

      $table->foreignUuid('courier_id')->nullable()->constrained('users')->nullOnDelete(); // nhân viên giao
      $table->string('carrier')->nullable();
      $table->string('tracking_no')->nullable();

      $table->string('status', 32)->index(); // pending|picking|shipped|delivered|failed|returned
      $table->unsignedBigInteger('shipping_cost_actual_vnd')->nullable();

      $table->timestamp('assigned_at')->nullable();
      $table->timestamp('picked_at')->nullable();
      $table->timestamp('shipped_at')->nullable();
      $table->timestamp('delivered_at')->nullable();

      $table->timestamps();

      $table->unique(['order_id']); // 1 đơn = 1 shipment
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('shipments');
  }
};
