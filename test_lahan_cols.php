<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;

// Simulate applyScope with operator scope = '11.01' (POLRES SURABAYA UTARA)
$scope = '11.01';

// applyScope for tanam stats
$tanamWithScope = DB::table('tanam')
    ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
    ->where('tanam.deletestatus', '1')
    ->where('lahan.deletestatus', '!=', '0')
    ->whereNotNull('tanam.valid_oleh')
    ->where('tanam.valid_oleh', '!=', '')
    ->where(function($q) use ($scope) {
        $q->where('lahan.id_tingkat', $scope)
          ->orWhere('lahan.id_tingkat', 'LIKE', $scope . '.%');
    })
    ->select('tanam.id_tanam', 'lahan.id_tingkat', 'tanam.luas_tanam')
    ->get();

echo "=== TANAM SCOPED TO '11.01' (should show bubutan records) ===\n";
foreach ($tanamWithScope as $t) {
    echo "id_tanam={$t->id_tanam} | id_tingkat={$t->id_tingkat} | luas={$t->luas_tanam}\n";
}

$tanamSum = DB::table('tanam')
    ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
    ->where('tanam.deletestatus', '1')
    ->where('lahan.deletestatus', '!=', '0')
    ->whereNotNull('tanam.valid_oleh')
    ->where('tanam.valid_oleh', '!=', '')
    ->where(function($q) use ($scope) {
        $q->where('lahan.id_tingkat', $scope)
          ->orWhere('lahan.id_tingkat', 'LIKE', $scope . '.%');
    })
    ->sum('tanam.luas_tanam');

echo "\nTotal for scope '11.01': $tanamSum HA\n";

// Check the applyScope function as it is in operator controller
// The scope is directly from user->id_tugas
// applyScope($query, 'lahan.id_tingkat') checks:
// WHERE lahan.id_tingkat = '11.01' OR lahan.id_tingkat LIKE '11.01.%'

// But what does the actual operator query look like?
// From the code in Operator/KelolaLahanController:
// $applyScope = function ($query, $column = 'lahan.id_tingkat') use ($scope) {
//     if ($scope && $scope != '0') {
//         return $query->where(function($q) use ($column, $scope) {
//             $q->where($column, $scope)->orWhere($column, 'LIKE', $scope . '.%');
//         });
//     }
//     return $query;
// };

// For tanam stats the column used is 'lahan.id_tingkat'
// This should be correct!

// Let's also check for panen and serapan
$panenSum = DB::table('panen')
    ->join('lahan', 'panen.id_lahan', '=', 'lahan.id_lahan')
    ->where('panen.deletestatus', '1')
    ->where('lahan.deletestatus', '!=', '0')
    ->whereNotNull('panen.valid_oleh')
    ->where('panen.valid_oleh', '!=', '')
    ->where(function($q) use ($scope) {
        $q->where('lahan.id_tingkat', $scope)
          ->orWhere('lahan.id_tingkat', 'LIKE', $scope . '.%');
    })
    ->sum('panen.luas_panen');
echo "Panen sum for scope '11.01': $panenSum HA\n";

// Admin scope is '0' or no scope at all
// Admin should see all data
$tanamAdmin = DB::table('tanam')
    ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
    ->where('tanam.deletestatus', '1')
    ->where('lahan.deletestatus', '!=', '0')
    ->whereNotNull('tanam.valid_oleh')
    ->where('tanam.valid_oleh', '!=', '')
    ->sum('tanam.luas_tanam');
echo "\nAdmin total (no scope): $tanamAdmin HA (should be 63)\n";

// Check admin id_tugas = '11' - does the scope apply?
// scope '11' means -> WHERE id_tingkat = '11' OR id_tingkat LIKE '11.%'
// All polres are like '11.01', '11.07', etc. which all start with '11.'
$scopeAdmin = '11';
$tanamScopeAdmin = DB::table('tanam')
    ->join('lahan', 'tanam.id_lahan', '=', 'lahan.id_lahan')
    ->where('tanam.deletestatus', '1')
    ->where('lahan.deletestatus', '!=', '0')
    ->whereNotNull('tanam.valid_oleh')
    ->where('tanam.valid_oleh', '!=', '')
    ->where(function($q) use ($scopeAdmin) {
        $q->where('lahan.id_tingkat', $scopeAdmin)
          ->orWhere('lahan.id_tingkat', 'LIKE', $scopeAdmin . '.%');
    })
    ->sum('tanam.luas_tanam');
echo "Admin with scope '11': $tanamScopeAdmin HA\n";
