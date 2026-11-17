<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Secuencia extends Model
{
    protected $fillable = [
        'codigo',
        'ultimo',
    ];

    protected $casts = [
        'ultimo' => 'integer',
    ];
}
