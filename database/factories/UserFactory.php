<?php

namespace Database\Factories;

use App\Models\User; // Pastikan Model User diimpor
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Password yang di-hash secara statis untuk mempercepat proses generating data.
     */
    protected static ?string $password;

    /**
     * Definisikan state default untuk model User.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'nrp' => fake()->unique()->numerify('########'), 
            'statusadmin' => '3', // Default: Anggota
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->statusadmin === '1') {
                $user->assignRole('admin');
            } elseif ($user->statusadmin === '2') {
                $user->assignRole('operator');
            } else {
                $user->assignRole('anggota');
            }
        });
    }

    /**
     * State khusus untuk Admin (statusadmin: 1).
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'statusadmin' => '1',
        ]);
    }

    /**
     * State khusus untuk Operator (statusadmin: 2).
     */
    public function operator(): static
    {
        return $this->state(fn (array $attributes) => [
            'statusadmin' => '2',
        ]);
    }

    /**
     * State khusus untuk Anggota (statusadmin: 3).
     */
    public function anggota(): static
    {
        return $this->state(fn (array $attributes) => [
            'statusadmin' => '3',
        ]);
    }
}