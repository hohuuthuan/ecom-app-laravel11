<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
  public function run(): void
  {
    $this->seedRole('Admin', 'Full access');
    $this->seedRole('Customer', 'Customer');
  }


  private function seedRole(string $name, string $description): void
  {
    $role = Role::firstOrCreate(['name' => $name], ['description' => $description]);
    if ($role->description !== $description) {
      $role->update(['description' => $description]);
    }
  }
}
