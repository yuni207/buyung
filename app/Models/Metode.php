<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metode extends Model
{
    protected $table = 'metode';

    protected $fillable = [
        'nama',
    ];
}
