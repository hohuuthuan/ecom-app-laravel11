<?php
// database/migrations/2025_09_14_000016_create_shipments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('shipments', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_id')->constrained('orders')->cascadeOnDelete();
      $table->string('carrier')->nullable();
      $table->string('tracking_number')->nullable();
      $table->string('status', 32)->default('PENDING');
      $table->timestamp('shipped_at')->nullable();
      $table->timestamp('delivered_at')->nullable();
      $table->timestamps();
      $table->index(['status', 'shipped_at']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('shipments');
  }
};
