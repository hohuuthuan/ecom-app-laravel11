<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('addresses', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $table->string('line1');
      $table->string('line2')->nullable();
      $table->string('city')->nullable();
      $table->string('state')->nullable();
      $table->string('postal_code', 32)->nullable();
      $table->char('country_code', 2)->default('VN');
      $table->boolean('is_default')->default(false)->index();
      $table->timestamps();

      // Tra cứu nhanh địa chỉ mặc định của 1 user
      $table->index(['user_id', 'is_default']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('addresses');
  }
};
