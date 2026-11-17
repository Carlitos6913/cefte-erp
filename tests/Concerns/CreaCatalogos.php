<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Lote;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UnitMeasure;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

trait CreaCatalogos
{
    protected function crearVariante(
        float $stock = 0,
        float $precio = 5.0,
        ?int $factorEmpaque = null,
    ): ProductVariant {
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

        $variante = ProductVariant::create([
            'product_id' => $producto->id,
            'unit_measure_id' => $unidad->id,
            'unidad_base_id' => $unidad->id,
            'unidad_empaque_id' => $factorEmpaque ? $this->crearCaja()->id : null,
            'factor_empaque' => $factorEmpaque,
            'sku' => 'SKU'.Str::upper(Str::random(8)),
            'minimum_stock' => 0,
            'unit_price' => $precio,
            'is_active' => true,
        ]);

        if ($stock > 0) {
            $this->crearLote($variante, $stock);
        }

        return $variante;
    }

    /**
     * Crea un lote con stock para una variante.
     */
    protected function crearLote(
        ProductVariant $variante,
        float $stock,
        ?string $codigo = null,
        ?string $fechaCaducidad = null,
    ): Lote {
        return Lote::create([
            'product_variant_id' => $variante->id,
            'codigo' => $codigo ?? 'L'.Str::upper(Str::random(6)),
            'fecha_caducidad' => $fechaCaducidad ?? now()->addMonths(6)->toDateString(),
            'cantidad_inicial' => $stock,
            'stock' => $stock,
            'is_active' => true,
        ]);
    }

    /**
     * Unidad de empaque de almacenaje compartida por las pruebas.
     */
    protected function crearCaja(): UnitMeasure
    {
        return UnitMeasure::firstOrCreate(
            ['name' => 'Caja'],
            ['symbol' => 'cj'],
        );
    }

    /**
     * Unidad base de stock compartida por las pruebas.
     */
    protected function crearUnidadBase(): UnitMeasure
    {
        return UnitMeasure::firstOrCreate(
            ['name' => 'Unidad'],
            ['symbol' => 'und'],
        );
    }

    /**
     * Los ingresos exigen un responsable del padrón de empleados.
     */
    protected function crearEncargado(bool $isActive = true): Employee
    {
        return Employee::create([
            'first_name' => 'Rosa',
            'first_surname' => 'Mamani',
            'identity_card' => Str::random(4).' LP',
            'is_active' => $isActive,
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
