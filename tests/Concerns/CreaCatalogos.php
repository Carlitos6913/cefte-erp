<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UnitMeasure;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

trait CreaCatalogos
{
    protected function crearVariante(float $stock = 0, float $precio = 5.0): ProductVariant
    {
        $categoria = Category::create([
            'code' => 'CAT'.Str::upper(Str::random(6)),
            'name' => 'Categoría '.Str::random(4),
        ]);

        $unidad = UnitMeasure::create([
            'name' => 'Unidad '.Str::random(4),
            'symbol' => 'und',
        ]);

        $producto = Product::create([
            'category_id' => $categoria->id,
            'code' => 'P'.Str::upper(Str::random(8)),
            'name' => 'Producto '.Str::random(5),
            'is_active' => true,
        ]);

        return ProductVariant::create([
            'product_id' => $producto->id,
            'unit_measure_id' => $unidad->id,
            'sku' => 'SKU'.Str::upper(Str::random(8)),
            'stock' => $stock,
            'minimum_stock' => 0,
            'unit_price' => $precio,
            'is_active' => true,
        ]);
    }

    protected function usuario(string $rol = 'Administrador'): User
    {
        Role::firstOrCreate(['name' => $rol, 'guard_name' => 'web']);

        $user = User::factory()->create();

        $user->assignRole($rol);

        return $user;
    }
}
