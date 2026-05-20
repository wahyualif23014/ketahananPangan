<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poktan extends Model
{
    use HasFactory;

    protected $table = 'poktan';
    protected $primaryKey = 'id_poktan';
    protected $guarded = [];
}
