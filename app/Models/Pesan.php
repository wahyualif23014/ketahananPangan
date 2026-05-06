<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function sender()
    {
        return $this->belongsTo(Anggota::class, 'sender_id', 'id_anggota');
    }

    public function recipient()
    {
        return $this->belongsTo(Anggota::class, 'recipient_id', 'id_anggota');
    }
}
