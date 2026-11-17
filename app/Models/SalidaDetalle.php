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
        'lote_id',
        'cantidad',
        'unidad_id',
        'cantidad_original',
        'cantidad_vendida',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'cantidad_original' => 'decimal:2',
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

    /**
     * Lote despachado en esta línea (FEFO puede repartir una
     * cantidad entre varios lotes, generando varias líneas).
     */
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class, 'lote_id');
    }

    /**
     * Unidad en la que el usuario capturó la cantidad.
     */
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class, 'unidad_id');
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
