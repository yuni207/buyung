<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HutangBayar extends Model
{
    protected $table = 'hutang_bayar';

    protected $fillable = [
        'id_hutang',
        'jumlah',
        'keterangan',
        'id_user',
    ];

    public function hutang()
    {
        return $this->belongsTo(Hutang::class, 'id_hutang');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
