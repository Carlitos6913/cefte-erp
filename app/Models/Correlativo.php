<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correlativo extends Model
{
    protected $table = 'correlativos';

    protected $fillable = [
        'codigo',
        'anio',
        'ultimo',
    ];

    protected $casts = [
        'anio' => 'integer',
        'ultimo' => 'integer',
    ];
}
