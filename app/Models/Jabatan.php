<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatan'; // Nama tabel di DB
    protected $primaryKey = 'id_jabatan'; // Primary key custom
    
    protected $fillable = [
        'nama_jabatan',
        'keterangan',
    ];
}