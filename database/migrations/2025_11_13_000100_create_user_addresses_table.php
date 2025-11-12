<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('user_addresses', function (Blueprint $table) {
      $table->uuid('id')->primary();                 // id?: uuid
      $table->uuid('user_id');                       // userId?: uuid (không ràng buộc FK)
      $table->string('address', 120);                // address: tên đặt (nhà riêng, công ty,...)
      $table->unsignedInteger('address_ward_id');    // addressWardId: id của ward
      $table->unsignedInteger('address_province_id'); // addressProvinceId: id của province
      $table->text('note')->nullable();              // note?: ghi chú có thể rỗng
      $table->boolean('default')->default(false);    // default: boolean
      $table->timestamps();                          // createdAt?, updatedAt?

      // Chỉ thêm index nhẹ để truy vấn theo user nhanh hơn (không có ràng buộc)
      $table->index('user_id');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('user_addresses');
  }
};
