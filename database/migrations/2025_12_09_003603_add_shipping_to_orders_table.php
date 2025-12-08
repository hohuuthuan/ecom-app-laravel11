<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('shipping_type', 20)
                ->nullable()
                ->after('payment_status'); // INTERNAL|EXTERNAL
            $table->uuid('shipper_id')
                ->nullable()
                ->after('shipping_type');

            $table->timestamp('shipping_started_at')
                ->nullable()
                ->after('shipper_id');

            $table->timestamp('delivery_failed_at')
                ->nullable()
                ->after('delivered_at');

            $table->string('delivery_failed_reason')
                ->nullable()
                ->after('delivery_failed_at');

            $table->index('shipper_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['shipper_id']);

            $table->dropColumn([
                'shipping_type',
                'shipper_id',
                'shipping_started_at',
                'delivery_failed_at',
                'delivery_failed_reason',
            ]);
        });
    }
};
