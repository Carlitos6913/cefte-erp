<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VentaDetalle extends Model
{
    protected $fillable = [
        'venta_id',
        'product_variant_id',
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

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Unidad en la que el usuario capturó la cantidad.
     */
    public function unidad(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class, 'unidad_id');
    }

    public function salidaDetalles(): BelongsToMany
    {
        return $this->belongsToMany(SalidaDetalle::class, 'salida_detalle_venta_detalle')
            ->withPivot('cantidad');
    }
}
