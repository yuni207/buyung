<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetorTarik extends Model
{
    protected $table = 'setor_tarik';

    protected $fillable = [
        'tanggal',
        'nama_pelanggan',
        'jenis',
        'total',
        'biaya_admin',
        'keterangan',
        'id_metode',
        'id_user',
        'bukti',
    ];

    public function metode()
    {
        return $this->belongsTo(Metode::class, 'id_metode');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
