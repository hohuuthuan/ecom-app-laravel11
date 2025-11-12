<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('wards', function (Blueprint $table) {
      $table->integer('id')->primary();                       // id INT(11) NOT NULL
      $table->integer('province_id')->nullable()->index();    // province_id INT(11), INDEX
      $table->string('name', 255)->nullable();                // name VARCHAR(255)
      $table->string('slug', 255)->nullable();                // slug VARCHAR(255)
      $table->string('type', 50)->nullable();                 // type VARCHAR(50)
      $table->string('name_with_type', 255)->nullable();      // name_with_type VARCHAR(255)
      $table->string('path', 255)->nullable();                // path VARCHAR(255)
      $table->string('path_with_type', 255)->nullable();      // path_with_type VARCHAR(255)

      $table->foreign('province_id')->references('id')->on('provinces');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('wards');
  }
};
