<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'discount_percent')) {
            return;
        }

        DB::statement("
            UPDATE `products`
            SET `discount_percent` = LEAST(100, GREATEST(0, ROUND(`discount_percent`)))
        ");

        DB::statement("
            ALTER TABLE `products`
            MODIFY `discount_percent` TINYINT UNSIGNED NOT NULL DEFAULT 0
            COMMENT 'Discount percent (0-100)'
        ");
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'discount_percent')) {
            return;
        }

        DB::statement("
            ALTER TABLE `products`
            MODIFY `discount_percent` DECIMAL(5,2) UNSIGNED NOT NULL DEFAULT 0
            COMMENT 'Discount percent (0-100)'
        ");
    }
};
