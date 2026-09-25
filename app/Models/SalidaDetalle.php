<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalidaDetalle extends Model
{
    protected $fillable = [
        'salida_id',
        'product_variant_id',
        'cantidad',
        'cantidad_vendida',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'cantidad_vendida' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function salida(): BelongsTo
    {
        return $this->belongsTo(Salida::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function ventaDetalles(): BelongsToMany
    {
        return $this->belongsToMany(VentaDetalle::class, 'salida_detalle_venta_detalle')
            ->withPivot('cantidad');
    }

    public function getSaldoDisponibleAttribute(): int
    {
        return $this->cantidad - $this->cantidad_vendida;
    }
}
