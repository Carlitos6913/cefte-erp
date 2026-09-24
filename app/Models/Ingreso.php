<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingreso extends Model
{
    protected $fillable = [
        'numero',
        'fecha',
        'proveedor',
        'observaciones',
        'total',
        'estado',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(IngresoDetalle::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
