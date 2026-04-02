<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisi Permissions
        Permission::create(['name' => 'manage anggota']);
        Permission::create(['name' => 'input laporan']);
        Permission::create(['name' => 'view dashboard']);

        // 2. Definisi Roles
        $adminRole = Role::create(['name' => 'admin']);
        $operatorRole = Role::create(['name' => 'operator']);
        $anggotaRole = Role::create(['name' => 'anggota']);

        // 3. Assign Permissions
        $adminRole->givePermissionTo(Permission::all());
        $operatorRole->givePermissionTo(['view dashboard', 'input laporan']);
        $anggotaRole->givePermissionTo(['view dashboard']);

        $admin = User::create([
            'name' => 'Wahyu Alif',
            'nrp' => '12345678', 
            'password' => Hash::make('password123'),
            'statusadmin' => '1',
        ]);
        $admin->assignRole($adminRole);

        $operator = User::create([
            'name' => 'Dio Vladika',
            'nrp' => '96050789',
            'password' => Hash::make('operator123'),
            'statusadmin' => '2',
        ]);
        $operator->assignRole($operatorRole);

        $anggota = User::create([
            'name' => 'dewa',
            'nrp' => '87654321',
            'password' => Hash::make('anggota123'),
            'statusadmin' => '3',
        ]);
        $anggota->assignRole($anggotaRole);

    }
}