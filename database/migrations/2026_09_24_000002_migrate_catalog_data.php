<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private array $unidadMap = [
        'gramo' => ['name' => 'Gramo', 'symbol' => 'g'],
        'gramos' => ['name' => 'Gramo', 'symbol' => 'g'],
        'g' => ['name' => 'Gramo', 'symbol' => 'g'],
        'kilogramo' => ['name' => 'Kilogramo', 'symbol' => 'kg'],
        'kilogramos' => ['name' => 'Kilogramo', 'symbol' => 'kg'],
        'kg' => ['name' => 'Kilogramo', 'symbol' => 'kg'],
        'litro' => ['name' => 'Litro', 'symbol' => 'l'],
        'litros' => ['name' => 'Litro', 'symbol' => 'l'],
        'l' => ['name' => 'Litro', 'symbol' => 'l'],
        'mililitro' => ['name' => 'Mililitro', 'symbol' => 'ml'],
        'mililitros' => ['name' => 'Mililitro', 'symbol' => 'ml'],
        'ml' => ['name' => 'Mililitro', 'symbol' => 'ml'],
        'unidad' => ['name' => 'Unidad', 'symbol' => 'und'],
        'unidades' => ['name' => 'Unidad', 'symbol' => 'und'],
        'und' => ['name' => 'Unidad', 'symbol' => 'und'],
        'caja' => ['name' => 'Caja', 'symbol' => 'cj'],
        'cajas' => ['name' => 'Caja', 'symbol' => 'cj'],
        'bolsa' => ['name' => 'Bolsa', 'symbol' => 'bol'],
        'bolsas' => ['name' => 'Bolsa', 'symbol' => 'bol'],
        'paquete' => ['name' => 'Paquete', 'symbol' => 'pq'],
        'paquetes' => ['name' => 'Paquete', 'symbol' => 'pq'],
    ];

    public function up(): void
    {
        if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        if (! Schema::hasTable('productos')) {
            return;
        }

        $this->migrarUnidadesMedida();
        $this->migrarCategorias();
        $this->migrarProductos();
        $this->reapuntarDetalles();
        $this->eliminarTablasViejas();
    }

    private function migrarUnidadesMedida(): void
    {
        $unidadesViejas = DB::table('productos')
            ->distinct()
            ->pluck('unidad_medida');

        foreach ($unidadesViejas as $unidad) {
            $clave = strtolower(trim($unidad));

            $def = $this->unidadMap[$clave] ?? [
                'name' => $unidad,
                'symbol' => Str::limit(Str::upper(Str::ascii($unidad)), 3, ''),
            ];

            DB::table('unit_measures')->updateOrInsert(
                ['name' => $def['name']],
                ['symbol' => $def['symbol'], 'created_at' => now(), 'updated_at' => now()],
            );
        }

        foreach ($this->unidadMap as $def) {
            DB::table('unit_measures')->updateOrInsert(
                ['name' => $def['name']],
                ['symbol' => $def['symbol'], 'created_at' => now(), 'updated_at' => now()],
            );
        }
    }

    private function migrarCategorias(): void
    {
        $categorias = DB::table('categorias')->orderBy('id')->get();

        $usados = DB::table('categories')->pluck('code')->all();

        foreach ($categorias as $categoria) {
            $nuevoId = DB::table('categorias_vieja_mapping')
                ->where('vieja_id', $categoria->id)
                ->value('nueva_id');

            if (! $nuevoId) {
                $code = $this->generarCodeCategoria($categoria->nombre, $usados);
                $usados[] = $code;

                $nuevoId = DB::table('categories')->insertGetId([
                    'code' => $code,
                    'name' => $categoria->nombre,
                    'description' => $categoria->descripcion,
                    'created_at' => $categoria->created_at ?: now(),
                    'updated_at' => $categoria->updated_at ?: now(),
                ]);
            }

            DB::table('categorias_vieja_mapping')->updateOrInsert(
                ['vieja_id' => $categoria->id],
                ['nueva_id' => $nuevoId],
            );
        }
    }

    private function migrarProductos(): void
    {
        $productos = DB::table('productos')->orderBy('id')->get();

        foreach ($productos as $producto) {
            $varianteExistente = DB::table('productos_vieja_mapping')
                ->where('vieja_id', $producto->id)
                ->value('nueva_variante_id');

            if ($varianteExistente) {
                continue;
            }

            $code = $this->generarCodeProducto($producto->codigo, DB::table('products')->pluck('code')->all());

            $categoriaNueva = DB::table('categorias_vieja_mapping')
                ->where('vieja_id', $producto->categoria_id)
                ->value('nueva_id');

            $productoId = DB::table('products')->insertGetId([
                'category_id' => $categoriaNueva,
                'code' => $code,
                'name' => Str::limit($producto->nombre, 150, ''),
                'description' => $producto->descripcion,
                'is_active' => $producto->activo,
                'created_at' => $producto->created_at ?: now(),
                'updated_at' => $producto->updated_at ?: now(),
            ]);

            $varianteId = DB::table('product_variants')->insertGetId([
                'product_id' => $productoId,
                'unit_measure_id' => $this->obtenerUnidadId($producto->unidad_medida),
                'sku' => $producto->codigo,
                'flavor' => null,
                'packaging_type' => null,
                'net_content' => null,
                'stock' => $producto->stock,
                'minimum_stock' => $producto->stock_minimo,
                'unit_price' => $producto->precio_venta,
                'is_active' => $producto->activo,
                'created_at' => $producto->created_at ?: now(),
                'updated_at' => $producto->updated_at ?: now(),
            ]);

            DB::table('productos_vieja_mapping')->updateOrInsert(
                ['vieja_id' => $producto->id],
                ['nueva_variante_id' => $varianteId],
            );
        }
    }

    private function reapuntarDetalles(): void
    {
        $tablas = [
            'ingreso_detalles',
            'salida_detalles',
            'venta_detalles',
        ];

        foreach ($tablas as $tabla) {
            if (Schema::hasColumn($tabla, 'producto_id')) {
                if ($this->tieneForeign($tabla, 'producto_id')) {
                    Schema::table($tabla, function (Blueprint $table) {
                        $table->dropForeign(['producto_id']);
                    });
                }

                Schema::table($tabla, function (Blueprint $table) {
                    $table->renameColumn('producto_id', 'product_variant_id');
                });
            }

            DB::statement("
                UPDATE {$tabla} d
                JOIN productos p ON p.id = d.product_variant_id
                JOIN product_variants v ON v.sku = p.codigo
                SET d.product_variant_id = v.id
            ");

            $constraint = "{$tabla}_product_variant_id_foreign";

            if (! $this->tieneConstraint($tabla, $constraint)) {
                Schema::table($tabla, function (Blueprint $table) {
                    $table->foreign('product_variant_id')
                        ->references('id')
                        ->on('product_variants')
                        ->restrictOnDelete();
                });
            }
        }
    }

    private function tieneForeign(string $tabla, string $columna): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $tabla)
            ->where('column_name', $columna)
            ->whereNotNull('referenced_table_name')
            ->exists();
    }

    private function tieneConstraint(string $tabla, string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('table_schema', DB::connection()->getDatabaseName())
            ->where('table_name', $tabla)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }

    private function eliminarTablasViejas(): void
    {
        if ($this->tieneConstraint('productos', 'productos_categoria_id_foreign')) {
            Schema::table('productos', function (Blueprint $table) {
                $table->dropForeign(['categoria_id']);
            });
        }

        Schema::dropIfExists('productos');
        Schema::dropIfExists('categorias');
    }

    private function obtenerUnidadId(string $unidadVieja): int
    {
        $clave = strtolower(trim($unidadVieja));

        $def = $this->unidadMap[$clave] ?? [
            'name' => $unidadVieja,
            'symbol' => Str::limit(Str::upper(Str::ascii($unidadVieja)), 3, ''),
        ];

        return DB::table('unit_measures')
            ->where('name', $def['name'])
            ->value('id');
    }

    private function generarCodeCategoria(string $nombre, array $usados): string
    {
        $base = $this->iniciales($nombre);

        return $this->unico($base, $usados);
    }

    private function generarCodeProducto(string $codigo, array $usados): string
    {
        $base = Str::upper(Str::ascii($codigo));
        $base = Str::limit($base, 30, '');

        if (strlen($base) < 3) {
            $base = str_pad($base, 3, 'X');
        }

        return $this->unico($base, $usados);
    }

    private function iniciales(string $texto): string
    {
        $limpio = Str::ascii(trim($texto));
        $palabras = preg_split('/\s+/', $limpio);

        $iniciales = '';

        foreach ($palabras as $palabra) {
            $iniciales .= Str::upper(Str::substr($palabra, 0, 1));
        }

        if (strlen($iniciales) < 3) {
            $iniciales = Str::upper(Str::limit($limpio, 3, ''));
        }

        return Str::limit($iniciales, 10, '');
    }

    private function unico(string $base, array $usados): string
    {
        $candidato = $base;
        $sufijo = 1;

        while (in_array($candidato, $usados)) {
            $sufijo++;
            $resto = Str::limit($base, 10 - strlen((string) $sufijo), '');
            $candidato = $resto.$sufijo;
        }

        return $candidato;
    }

    public function down(): void
    {
        foreach (['ingreso_detalles', 'salida_detalles', 'venta_detalles'] as $tabla) {
            if (Schema::hasColumn($tabla, 'product_variant_id')) {
                $constraint = "{$tabla}_product_variant_id_foreign";

                if ($this->tieneConstraint($tabla, $constraint)) {
                    Schema::table($tabla, function (Blueprint $table) {
                        $table->dropForeign(['product_variant_id']);
                    });
                }

                Schema::table($tabla, function (Blueprint $table) {
                    $table->renameColumn('product_variant_id', 'producto_id');
                });
            }
        }
    }
};
