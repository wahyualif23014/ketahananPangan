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
        // Bersihkan cache permission agar perubahan langsung terbaca
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Definisikan Permissions
        Permission::create(['name' => 'manage anggota']);
        Permission::create(['name' => 'input laporan']);
        Permission::create(['name' => 'view dashboard']);

        // 2. Definisikan Roles (view, admin, operator)
        $adminRole = Role::create(['name' => 'admin']);
        $operatorRole = Role::create(['name' => 'operator']);
        $viewRole = Role::create(['name' => 'view']);

        // 3. Assign Permissions ke Roles
        $adminRole->givePermissionTo(Permission::all());
        $operatorRole->givePermissionTo(['view dashboard', 'input laporan']);
        $viewRole->givePermissionTo(['view dashboard']);

        // 4. Buat User Contoh untuk masing-masing Role

        // Admin
        $admin = User::create([
            'id_jabatan' => 1,
            'id_tugas' => 1,
            'nama_anggota' => 'Wahyu Alif',
            'username' => '12345678',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
        $admin->assignRole($adminRole);

        // Operator
        $operator = User::create([
            'id_jabatan' => 2,
            'id_tugas' => 2,
            'nama_anggota' => 'Dio Vladika',
            'username' => '96050789',
            'password' => Hash::make('operator123'),
            'role' => 'operator',
        ]);
        $operator->assignRole($operatorRole);

        // View
        $viewUser = User::create([
            'id_jabatan' => 3,
            'id_tugas' => 3,
            'nama_anggota' => 'Pengamat Data',
            'username' => '87654321',
            'password' => Hash::make('view123'),
            'role' => 'view',
        ]);
        $viewUser->assignRole($viewRole);
    }
}
