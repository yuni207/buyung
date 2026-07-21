<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    protected $table = 'piutang';

    protected $fillable = [
        'nama_peminjam',
        'no_hp',
        'keterangan',
        'total',
        'terbayar',
        'status',
        'id_pengeluaran',
        'id_user',
        'tanggal',
        'jatuh_tempo',
    ];

    public function bayar()
    {
        return $this->hasMany(PiutangBayar::class, 'id_piutang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
