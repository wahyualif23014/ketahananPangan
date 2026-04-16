<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komoditi extends Model
{
    use HasFactory;

    protected $table = 'komoditi';
    protected $primaryKey = 'id_komoditi';
    public $timestamps = false; // We are updating datetransaction manually

    protected $fillable = [
        'jenis_komoditi',
        'nama_komoditi',
        'datetransaction',
        'deletestatus'
    ];
}
