<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('warehouse_receipt', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('tax_identification_number')->nullable();
      $table->string('name_of_delivery_person')->nullable();
      $table->string('delivery_unit')->nullable();
      $table->string('address')->nullable();
      $table->string('delivery_note_number')->nullable();
      $table->string('warehouse_from')->nullable();
      $table->foreignUuid('supplier_id')->constrained('suppliers')->restrictOnDelete();
      $table->decimal('sub_total', 15, 2)->default(0);
      $table->timestamps(); // created_at theo bạn mô tả; dùng timestamps cho đồng bộ
    });
  }
  public function down() { Schema::dropIfExists('warehouse_receipt'); }
};
