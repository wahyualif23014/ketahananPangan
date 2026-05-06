<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TingkatKesatuan extends Model
{
    use HasFactory;

    protected $table = 'tingkat';
    protected $primaryKey = 'id_tingkat';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id_tingkat',
        'nama_tingkat',
    ];
}
