<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $table = 'hutang';

    protected $fillable = [
        'nama_pelanggan',
        'no_hp',
        'keterangan',
        'total',
        'terbayar',
        'status',
        'id_transaksi',
        'id_user',
        'tanggal',
        'jatuh_tempo',
    ];

    public function bayar()
    {
        return $this->hasMany(HutangBayar::class, 'id_hutang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
