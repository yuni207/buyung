<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiutangBayar extends Model
{
    protected $table = 'piutang_bayar';

    protected $fillable = [
        'id_piutang',
        'jumlah',
        'keterangan',
        'id_metode',
        'id_user',
    ];

    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'id_piutang');
    }

    public function metode()
    {
        return $this->belongsTo(Metode::class, 'id_metode');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
