<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('users', function (Blueprint $table) {
			$table->uuid('id')->primary();

			$table->string('email')->unique();
			$table->string('password');
			$table->string('full_name');
			$table->string('phone')->nullable();
			$table->string('address')->nullable();
			$table->string('avatar')->nullable();
			$table->string('status')->default('ACTIVE');
			$table->rememberToken();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('users');
	}
};
