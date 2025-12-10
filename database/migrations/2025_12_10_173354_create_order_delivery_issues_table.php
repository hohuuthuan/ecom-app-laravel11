<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_delivery_issues', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('order_id');
            $table->string('issue_type', 50);
            $table->text('reason')->nullable();
            $table->string('order_payment_method', 20);
            $table->unsignedBigInteger('order_grand_total_vnd')->default(0);
            $table->unsignedBigInteger('order_shipping_fee_vnd')->default(0);
            $table->unsignedBigInteger('refund_amount_vnd')->default(0);
            $table->unsignedBigInteger('lost_shipping_fee_vnd')->default(0);
            $table->boolean('is_refunded')->default(false);
            $table->timestamp('refunded_at')->nullable();
            $table->timestamp('issued_at');

            $table->timestamps();

            $table->foreign('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnDelete();

            $table->index('order_id');
            $table->index('issue_type');
            $table->index('order_payment_method');
            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_delivery_issues');
    }
};
