<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';
    public $incrementing = false; // Karena kamu ingin isi ID manual

    protected $fillable = [
        'id_anggota',
        'id_jabatan',
        'id_tugas',
        'nama_anggota',
        'no_telp_anggota',
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getAuthIdentifierName()
    {
        return 'username';
    }

    // Fungsi bantu untuk cek role manual di Controller/Blade
    public function hasRole($role)
    {
        return $this->role === $role;
    }
}
