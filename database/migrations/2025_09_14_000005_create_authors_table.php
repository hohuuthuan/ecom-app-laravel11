<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('authors', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name');
      $table->string('slug')->unique();
      $table->string('image');
      $table->text('description')->nullable();
      $table->string('status', 32)->default('ACTIVE');
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('authors');
  }
};
