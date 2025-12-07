<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // mỗi review gắn với 1 đơn cụ thể (có thể null với dữ liệu cũ)
            $table->foreignUuid('order_id')
                ->nullable()
                ->after('user_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // index phục vụ query theo order + product + user
            $table->index(
                ['order_id', 'product_id', 'user_id'],
                'reviews_order_product_user_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropIndex('reviews_order_product_user_idx');
            $table->dropColumn('order_id');
        });
    }
};
