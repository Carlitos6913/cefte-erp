<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UnitMeasure extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'symbol',
    ];

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'unit_measure_id');
    }
}
