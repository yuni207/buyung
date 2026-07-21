<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasirSession extends Model
{
    protected $table = 'kasir_session';

    protected $fillable = [
        'id_user',
        'modal_awal',
        'pemasukan_sesi',
        'pengeluaran_sesi',
        'uang_akhir',
        'setor_owner',
        'selisih',
        'keterangan_buka',
        'keterangan_tutup',
        'id_metode',
        'id_pemasukan_buka',
        'waktu_buka',
        'waktu_tutup',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
