<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('discounts', function (Blueprint $table) {
      $table->unsignedInteger('usage_limit')->nullable()->after('min_order_value_vnd');
    });
  }

  public function down(): void
  {
    Schema::table('discounts', function (Blueprint $table) {
      $table->dropColumn('usage_limit');
    });
  }
};
