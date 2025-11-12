<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('provinces', function (Blueprint $table) {
      $table->integer('id')->primary();                 // id INT(11) NOT NULL
      $table->string('name', 255)->nullable();          // name VARCHAR(255)
      $table->string('name_slug', 255)->nullable();     // name_slug VARCHAR(255)
      $table->string('full_name', 255)->nullable();     // full_name VARCHAR(255)
      $table->string('type', 50)->nullable();           // type VARCHAR(50)
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('provinces');
  }
};
