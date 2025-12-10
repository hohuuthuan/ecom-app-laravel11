<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (!Schema::hasColumn('orders', 'shipping_type')) {
                $table->string('shipping_type', 20)
                    ->nullable()
                    ->after('payment_status'); // INTERNAL|EXTERNAL
            }

            if (!Schema::hasColumn('orders', 'shipper_id')) {
                $table->uuid('shipper_id')
                    ->nullable()
                    ->after('shipping_type');

                $table->index('shipper_id');
            }

            if (!Schema::hasColumn('orders', 'shipping_started_at')) {
                $table->timestamp('shipping_started_at')
                    ->nullable()
                    ->after('shipper_id');
            }

            if (!Schema::hasColumn('orders', 'delivery_failed_at')) {
                $table->timestamp('delivery_failed_at')
                    ->nullable()
                    ->after('delivered_at');
            }

            if (!Schema::hasColumn('orders', 'delivery_failed_reason')) {
                $table->string('delivery_failed_reason')
                    ->nullable()
                    ->after('delivery_failed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            if (Schema::hasColumn('orders', 'shipper_id')) {
                $table->dropIndex(['shipper_id']);
                $table->dropColumn('shipper_id');
            }

            if (Schema::hasColumn('orders', 'shipping_type')) {
                $table->dropColumn('shipping_type');
            }

            if (Schema::hasColumn('orders', 'shipping_started_at')) {
                $table->dropColumn('shipping_started_at');
            }

            if (Schema::hasColumn('orders', 'delivery_failed_at')) {
                $table->dropColumn('delivery_failed_at');
            }

            if (Schema::hasColumn('orders', 'delivery_failed_reason')) {
                $table->dropColumn('delivery_failed_reason');
            }
        });
    }
};
