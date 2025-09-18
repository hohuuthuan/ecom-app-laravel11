<?php
// database/migrations/2025_09_14_000004_create_addresses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
  public function up(): void
  {
    Schema::create('addresses', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $table->string('full_name');
      $table->string('phone', 32)->nullable();
      $table->string('line1');
      $table->string('line2')->nullable();
      $table->string('city');
      $table->string('state')->nullable();
      $table->string('postal_code', 32)->nullable();
      $table->string('country_code', 2)->default('VN');
      $table->boolean('is_default')->default(false);
      $table->timestamps();
    });
  }
  public function down(): void
  {
    Schema::dropIfExists('addresses');
  }
};
