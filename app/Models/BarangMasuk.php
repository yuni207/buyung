<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $table = 'barang_masuk';

    protected $fillable = [
        'id_detail_barang',
        'jumlah',
        'tanggal',
        'keterangan',
        'id_user',
    ];

    public function detailBarang()
    {
        return $this->belongsTo(DetailBarang::class, 'id_detail_barang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
