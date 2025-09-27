<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('roles')->upsert(
            [
                ['id' => (string) Str::orderedUuid(), 'name' => 'Admin',    'description' => 'Full access', 'created_at' => $now, 'updated_at' => $now],
                ['id' => (string) Str::orderedUuid(), 'name' => 'Customer', 'description' => 'Customer',    'created_at' => $now, 'updated_at' => $now],
            ],
            ['name'],
            ['description', 'updated_at']
        );
    }
}
