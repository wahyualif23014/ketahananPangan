<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable {
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';

    public $incrementing = false;

    protected $keyType = 'int';

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

    // Beritahu Laravel untuk login menggunakan kolom 'username'

    public function getAuthIdentifierName() {
        return 'username';
    }

    // Fungsi cek role untuk navigasi/middleware

    public function hasRole( $role ) {
        return $this->role === $role;
    }

    public function jabatan() {
        // Menghubungkan id_jabatan di tabel anggota ke id_jabatan di tabel jabatan
        return $this->belongsTo( Jabatan::class, 'id_jabatan', 'id_jabatan' );
    }
}
