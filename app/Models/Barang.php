<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'kode',
        'barcode',
        'nama',
        'id_user',
    ];

    public function detailBarang()
    {
        return $this->hasMany(DetailBarang::class, 'id_barang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
