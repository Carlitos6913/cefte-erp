<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'unit_measure_id',
        'sku',
        'flavor',
        'packaging_type',
        'net_content',
        'stock',
        'minimum_stock',
        'unit_price',
        'is_active',
    ];

    protected $casts = [
        'net_content' => 'decimal:2',
        'stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unitMeasure(): BelongsTo
    {
        return $this->belongsTo(UnitMeasure::class, 'unit_measure_id');
    }
}
