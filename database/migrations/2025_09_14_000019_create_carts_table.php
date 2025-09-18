<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('carts', function (Blueprint $table) {
      $table->uuid('id')->primary();
      $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
      $table->string('token', 64)->nullable()->unique();
      $table->string('status', 32)->default('ACTIVE');
      $table->timestamp('expires_at')->nullable();
      $table->timestamps();
      $table->index(['user_id', 'status']);
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('carts');
  }
};
