<?php
// database/migrations/2025_09_14_000006_create_brands_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('brands', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name');
      $table->string('slug')->unique();
      $table->text('description')->nullable();
      $table->string('status', 32)->default('ACTIVE');
      $table->timestamps();
      $table->unique(['name']);
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('brands');
  }
};
