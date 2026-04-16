<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClearDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Ambil semua nama tabel
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();
        $key = 'Tables_in_' . $databaseName;

        foreach ($tables as $table) {
            $tableName = $table->$key;

            // Skip tabel migrations biar nggak kacau
            if ($tableName !== 'migrations') {
                DB::table($tableName)->truncate();
            }
        }

        // Enable lagi foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
