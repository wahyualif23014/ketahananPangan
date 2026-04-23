<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table = 'anggota';
    
    protected $primaryKey = 'id_anggota';
    
    public $incrementing = false; 
    
    protected $keyType = 'string';

    protected $fillable = [
        'id_anggota',
        'id_jabatan',
        'id_tugas',
        'nama_anggota',
        'no_telp_anggota',
        'username',
        'password',
        'role',
        'deletestatus',
        'datetransaction',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class, 'id_jabatan', 'id_jabatan');
    }
}