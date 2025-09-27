<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('purchase_receipts', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('publisher_id')->nullable()
        ->constrained('publishers')->nullOnDelete();
      $table->foreignUuid('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
      $table->timestamp('received_at');
      $table->string('name_of_delivery_person')->nullable();
      $table->string('delivery_unit')->nullable();
      $table->string('address_of_delivery_person')->nullable();
      $table->string('delivery_note_number')->nullable();
      $table->string('tax_identification_number')->nullable();
      $table->unsignedBigInteger('sub_total_vnd')->default(0);
      $table->foreignUuid('created_by')->constrained('users')->cascadeOnDelete();
      $table->timestamps();

      $table->index(['warehouse_id', 'received_at']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('purchase_receipts');
  }
};
