<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasukan extends Model
{
    protected $table = 'pemasukan';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'total',
        'id_metode',
        'id_transaksi',
        'id_user',
    ];

    public function metode()
    {
        return $this->belongsTo(Metode::class, 'id_metode');
    }

    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
