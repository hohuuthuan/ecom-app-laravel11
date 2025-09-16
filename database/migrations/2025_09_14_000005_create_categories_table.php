<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('categories', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name');
      $table->string('slug')->unique();
      $table->uuid('parent_id')->nullable();
      $table->text('description')->nullable();
      $table->string('image')->nullable();
      $table->string('status', 32)->default('ACTIVE');
      $table->timestamps();
      $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
      $table->unique(['parent_id', 'name']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('categories');
  }
};
