<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('order_batches', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('order_detail_id')->constrained('order_detail')->cascadeOnDelete();
      $table->foreignUuid('batch_id')->constrained('batches')->restrictOnDelete();
      $table->integer('quantity')->default(0);
      $table->timestamps();

      $table->unique(['order_detail_id','batch_id']);
    });
  }
  public function down() { Schema::dropIfExists('order_batches'); }
};

