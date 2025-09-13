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
        DB::table('roles')->upsert(
            [
                ['id' => Str::uuid(), 'name' => 'Admin', 'description' => 'Full access', 'created_at' => now(), 'updated_at' => now()],
                ['id' => Str::uuid(), 'name' => 'Customer', 'description' => 'Customer', 'created_at' => now(), 'updated_at' => now()],
            ],
            ['name'],
            ['description', 'updated_at']
        );
    }
}
