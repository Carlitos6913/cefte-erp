<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Salida extends Model
{
    protected $fillable = [
        'numero',
        'fecha',
        'motivo',
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
        return $this->hasMany(SalidaDetalle::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public const MOTIVO_VENTA = 'venta';

    public function autorizaVenta(): bool
    {
        return $this->motivo === self::MOTIVO_VENTA && $this->estado === 'activo';
    }
}
