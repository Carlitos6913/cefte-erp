<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'product_variant_id',
        'entrada',
        'salida',
        'stock_anterior',
        'stock_nuevo',
        'referencia',
        'referencia_id',
        'numero',
        'user_id',
    ];

    protected $casts = [
        'entrada' => 'decimal:2',
        'salida' => 'decimal:2',
        'stock_anterior' => 'decimal:2',
        'stock_nuevo' => 'decimal:2',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
