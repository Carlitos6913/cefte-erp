<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_measure_id',
        'unidad_base_id',
        'unidad_empaque_id',
        'factor_empaque',
        'sku',
        'flavor',
        'packaging_type',
        'net_content',
        'minimum_stock',
        'unit_price',
        'is_active',
    ];

    protected $casts = [
        'net_content' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'factor_empaque' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * `stock` y `presentacion` son derivados y deben viajar en el JSON.
     */
    protected $appends = ['stock', 'presentacion'];

    /**
     * Lotes de la variante. Su stock es la fuente de verdad.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class, 'product_variant_id');
    }

    /**
     * Stock disponible de la variante: suma del stock de sus lotes.
     *
     * Cuando el atributo `stock_agregado` viene precargado (por una
     * consulta con subselect) se usa ese valor para evitar N+1.
     */
    public function getStockAttribute(): float
    {
        if (array_key_exists('stock_agregado', $this->attributes)) {
            return round((float) $this->attributes['stock_agregado'], 2);
        }

        if ($this->relationLoaded('lotes')) {
            return round((float) $this->lotes->sum('stock'), 2);
        }

        return round((float) $this->lotes()->sum('stock'), 2);
    }

    /**
     * Precarga el stock de cada variante como subconsulta, sin traer
     * todos los lotes. Uso: ProductVariant::query()->conStock().
     */
    public function scopeConStock(Builder $query): Builder
    {
        return $query->addSelect([
            'stock_agregado' => Lote::query()
                ->selectRaw('COALESCE(SUM(stock), 0)')
                ->whereColumn('lotes.product_variant_id', 'product_variants.id'),
        ]);
    }

    /**
     * Variantes con stock disponible (> 0).
     */
    public function scopeConDisponible(Builder $query): Builder
    {
        return $query->whereHas('lotes', fn (Builder $q) => $q->where('stock', '>', 0));
    }

    /**
     * Unidades capturables por el usuario, lista para el frontend.
     *
     * @return array<int, array{id:int, name:string, symbol:string, factor:float}>
     */
    public function unidadesCapturables(): array
    {
        $unidades = [];

        if ($this->unidadBase) {
            $unidades[] = [
                'id' => (int) $this->unidadBase->id,
                'name' => $this->unidadBase->name,
                'symbol' => $this->unidadBase->symbol,
                'factor' => 1.0,
            ];
        }

        $factor = $this->factor_empaque === null ? null : (float) $this->factor_empaque;

        if ($this->unidadEmpaque && $factor !== null && $factor > 0) {
            $unidades[] = [
                'id' => (int) $this->unidadEmpaque->id,
                'name' => $this->unidadEmpaque->name,
                'symbol' => $this->unidadEmpaque->symbol,
                'factor' => $factor,
            ];
        }

        return $unidades;
    }

    /**
     * Presentación legible de la variante: sabor, envase y contenido.
     *
     * Ejemplo: "Clásico · Botella PET 1 l". Se usa en los selectores
     * para distinguir variantes que comparten el mismo producto.
     */
    public function getPresentacionAttribute(): string
    {
        $partes = [];

        if ($this->flavor) {
            $partes[] = Str::ucfirst($this->flavor);
        }

        $empaque = Str::ucfirst((string) $this->packaging_type);
        $contenido = '';

        if ($this->net_content !== null && (float) $this->net_content > 0) {
            $numero = rtrim(
                rtrim(number_format((float) $this->net_content, 2, '.', ''), '0'),
                '.'
            );

            $simbolo = $this->unitMeasure?->symbol;

            $contenido = $simbolo ? "{$numero} {$simbolo}" : $numero;
        }

        $envaseContenido = trim($empaque.' '.$contenido);

        if ($envaseContenido !== '') {
            $partes[] = $envaseContenido;
        }

        return implode(' · ', $partes);
    }

    /**
     * Stock expresado en el empaque (por ejemplo, cajas completas).
     */
    public function getStockEnEmpaqueAttribute(): float
    {
        $factor = $this->factor_empaque === null ? null : (float) $this->factor_empaque;

        if ($factor === null || $factor <= 0) {
            return 0.0;
        }

        return round((float) $this->stock / $factor, 2);
    }

    /**
     * Precio de una unidad base expresado en el empaque.
     */
    public function getPrecioPorEmpaqueAttribute(): float
    {
        $factor = $this->factor_empaque === null ? null : (float) $this->factor_empaque;

        if ($factor === null || $factor <= 0) {
            return 0.0;
        }

        return round((float) $this->unit_price * $factor, 2);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unitMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class, 'unit_measure_id');
    }

    /**
     * Unidad en la que se controla y se vende el stock.
     */
    public function unidadBase(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class, 'unidad_base_id');
    }

    /**
     * Unidad de almacenaje que agrupa varias unidades base (la caja).
     */
    public function unidadEmpaque(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class, 'unidad_empaque_id');
    }
}
