<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $fillable = [
        'numero',
        'fecha',
        'cliente',
        'cliente_id',
        'observaciones',
        'total',
        'estado',
        'pago_tipo',
        'saldo_pendiente',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
        'saldo_pendiente' => 'decimal:2',
    ];

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function clienteRelacion(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
