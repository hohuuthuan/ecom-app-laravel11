<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
  public function run(int $count = 10): void
  {
    $customerRole = Role::where('name', 'customer')->first();

    if (!$customerRole) {
      $this->command->error('Role "customer" chưa tồn tại trong bảng roles.');
      return;
    }

    for ($i = 1; $i <= $count; $i++) {
      $user = User::create([
        'id'        => Str::uuid(),
        'full_name' => fake()->name(),
        'email'     => fake()->unique()->safeEmail(),
        'password'  => Hash::make('112233'),
        'phone'     => fake()->phoneNumber(),
        'address'   => fake()->address(),
        'avatar'    => null,
        'status'    => 'ACTIVE',
      ]);

      // Gán role customer
      $user->roles()->attach($customerRole->id);
    }

    $this->command->info("Đã tạo {$count} user với role customer.");
  }
}