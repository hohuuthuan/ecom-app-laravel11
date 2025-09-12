<?php

namespace Database\Seeders;
// database/seeders/RoleSeeder.php
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        // database/seeders/RoleSeeder.php
        DB::table('roles')->updateOrInsert(
            ['name' => 'Admin'],
            ['id' => Str::uuid(), 'description' => 'Full access', 'updated_at' => now(), 'created_at' => now()]
        );

        DB::table('roles')->updateOrInsert(
            ['name' => 'Customer'],
            ['id' => Str::uuid(), 'description' => 'Customer', 'updated_at' => now(), 'created_at' => now()]
        );
    }
}
