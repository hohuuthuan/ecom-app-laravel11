<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('suppliers', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('email')->nullable();
      $table->string('phone')->nullable();
      $table->string('name_of_representative')->nullable();
      $table->string('address')->nullable();
      $table->string('status')->default('ACTIVE');
      $table->timestamps();
    });
  }
  public function down() { Schema::dropIfExists('suppliers'); }
};

