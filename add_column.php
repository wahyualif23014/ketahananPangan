<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasColumn('tanam', 'is_active')) {
    DB::statement('ALTER TABLE tanam ADD COLUMN is_active TINYINT(1) DEFAULT 1');
    echo "Column is_active added.\n";
} else {
    echo "Column already exists.\n";
}
