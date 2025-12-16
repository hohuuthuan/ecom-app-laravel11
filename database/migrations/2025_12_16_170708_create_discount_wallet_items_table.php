<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discount_wallet_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->uuid('discount_id');
            $table->uuid('user_id');

            $table->string('status', 32)->default('SAVED');

            $table->uuid('reserved_order_id')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamp('reserved_at')->nullable();

            $table->timestamps();

            $table->unique(['discount_id', 'user_id']);

            $table->index(['user_id', 'status']);
            $table->index(['reserved_order_id']);

            $table->foreign('discount_id')
                ->references('id')
                ->on('discounts')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discount_wallet_items');
    }
};
