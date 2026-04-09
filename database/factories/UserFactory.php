<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'id_jabatan' => 1, // Pastikan ID ini ada di tabel jabatan
            'id_tugas' => 1,
            'nama_anggota' => fake()->name(),
            'no_telp_anggota' => fake()->phoneNumber(),
            'username' => fake()->unique()->numerify('########'),
            'password' => static::$password ??= Hash::make('password'),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole('anggota');
        });
    }
}
