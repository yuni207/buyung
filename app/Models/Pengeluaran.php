<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';

    protected $fillable = [
        'tanggal',
        'keterangan',
        'total',
        'id_metode',
        'id_user',
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
