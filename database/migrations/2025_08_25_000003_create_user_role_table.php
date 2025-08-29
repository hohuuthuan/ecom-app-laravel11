<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_user_role_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up() {
    Schema::create('user_role', function (Blueprint $table) {
      $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
      $table->timestamps();
      $table->primary(['user_id','role_id']);
    });
  }
  public function down() { Schema::dropIfExists('user_role'); }
};
