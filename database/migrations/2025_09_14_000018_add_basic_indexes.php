<?php
// database/migrations/2025_09_14_000018_add_basic_indexes.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->index(['publisher_id']);
    });
    Schema::table('orders', function (Blueprint $table) {
      $table->index(['user_id']);
    });
  }
  public function down(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->dropIndex(['publisher_id']);
    });
    Schema::table('orders', function (Blueprint $table) {
      $table->dropIndex(['user_id']);
    });
  }
};
