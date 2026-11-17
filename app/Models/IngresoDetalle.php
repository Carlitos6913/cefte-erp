<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngresoDetalle extends Model
{
    protected $fillable = [
        'ingreso_id',
        'product_variant_id',
        'lote_id',
        'cantidad',
        'unidad_id',
        'cantidad_original',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'cantidad_original' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function ingreso(): BelongsTo
    {
        return $this->belongsTo(Ingreso::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Lote con caducidad creado para esta línea de recepción.
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
}
