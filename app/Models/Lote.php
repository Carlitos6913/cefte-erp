<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Lote de mercadería con caducidad.
 *
 * Es la fuente de verdad del stock: el stock de una variante es la
 * suma del stock de sus lotes. El despacho usa FEFO ordenando por
 * `fecha_caducidad` ascendente.
 */
class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'product_variant_id',
        'codigo',
        'fecha_caducidad',
        'cantidad_inicial',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'fecha_caducidad' => 'date',
        'cantidad_inicial' => 'decimal:2',
        'stock' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function ingresosDetalle(): HasMany
    {
        return $this->hasMany(IngresoDetalle::class, 'lote_id');
    }

    public function salidasDetalle(): HasMany
    {
        return $this->hasMany(SalidaDetalle::class, 'lote_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'lote_id');
    }

    /**
     * Lotes con stock disponible, ordenados por FEFO (caduca antes,
     * sale antes) y en segundo lugar por antigüedad de creación.
     */
    public function scopeDisponibleFefo(Builder $query): Builder
    {
        return $query->where('stock', '>', 0)
            ->orderBy('fecha_caducidad')
            ->orderBy('id');
    }
}
