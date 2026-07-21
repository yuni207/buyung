<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';

    protected $fillable = [
        'tanggal',
        'pukul',
        'nama',
        'total',
        'potongan',
        'bayar',
        'kembali',
        'status',
        'id_metode',
        'id_user',
    ];

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
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
