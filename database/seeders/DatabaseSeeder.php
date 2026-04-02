<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Memanggil RoleSeeder agar role & user admin otomatis terbuat
        $this->call([
            RoleSeeder::class,
        ]);
    }
}

