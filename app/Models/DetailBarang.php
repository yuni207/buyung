<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailBarang extends Model
{
    protected $table = 'detail_barang';

    protected $fillable = [
        'id_barang',
        'id_satuan',
        'stock',
        'harga_modal',
        'harga_jual',
        'harga_khusus',
        'id_user',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'id_barang');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
