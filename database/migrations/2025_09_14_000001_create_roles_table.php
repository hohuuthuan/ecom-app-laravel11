<?php
// database/migrations/2025_09_14_000001_create_roles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('roles', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->string('name')->unique();
      $table->string('description')->nullable();
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('roles');
  }
};
