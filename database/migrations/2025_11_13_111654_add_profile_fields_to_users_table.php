<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // sau phone để nhóm thông tin cá nhân chung với nhau
            $table->date('birthday')->nullable()->after('phone');
            $table->string('gender', 10)->nullable()->after('birthday');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birthday', 'gender']);
        });
    }
};
