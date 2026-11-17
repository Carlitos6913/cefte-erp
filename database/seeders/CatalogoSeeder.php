<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UnitMeasure;
use App\Services\CodigoService;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    /**
     * Catálogo de una planta de procesamiento de alimentos. Cada
     * producto declara sus presentaciones: la variante distingue por
     * tamaño, envase o sabor según el producto. El código del
     * producto y el SKU se generan automáticamente.
     *
     * `caja` indica cuántas unidades base trae la caja con la que se
     * almacena el producto. El stock se controla siempre en unidades
     * (una botella, una lata, una bolsa) y la caja solo permite
     * capturar ingresos y ventas en múltiplos de ese factor.
     */
    private array $catalogo = [
        [
            'categoria' => [
                'code' => 'BEB',
                'name' => 'Bebidas y Jugos',
                'description' => 'Jugos, néctares y bebidas envasadas de la línea de líquidos.',
            ],
            'productos' => [
                ['name' => 'Jugo de Mango', 'variantes' => [
                    ['unidad' => 'Mililitro', 'empaque' => 'Botella PET', 'contenido' => 250, 'sabor' => 'Clásico', 'precio' => 4.50, 'minimo' => 40, 'caja' => 24],
                    ['unidad' => 'Litro', 'empaque' => 'Botella PET', 'contenido' => 1, 'sabor' => 'Clásico', 'precio' => 13.00, 'minimo' => 24, 'caja' => 12],
                ]],
                ['name' => 'Jugo de Durazno', 'variantes' => [
                    ['unidad' => 'Litro', 'empaque' => 'Botella PET', 'contenido' => 1, 'sabor' => 'Natural', 'precio' => 12.50, 'minimo' => 20, 'caja' => 12],
                ]],
                ['name' => 'Jugo de Naranja', 'variantes' => [
                    ['unidad' => 'Litro', 'empaque' => 'Botella PET', 'contenido' => 1, 'sabor' => 'Natural', 'precio' => 12.00, 'minimo' => 20, 'caja' => 12],
                ]],
                ['name' => 'Bebida de Maca', 'variantes' => [
                    ['unidad' => 'Mililitro', 'empaque' => 'Botella PET', 'contenido' => 250, 'sabor' => 'Original', 'precio' => 5.00, 'minimo' => 30, 'caja' => 24],
                ]],
                ['name' => 'Soda de Frutas', 'variantes' => [
                    ['unidad' => 'Mililitro', 'empaque' => 'Botella PET', 'contenido' => 250, 'sabor' => 'Naranja', 'precio' => 3.80, 'minimo' => 36, 'caja' => 24],
                    ['unidad' => 'Mililitro', 'empaque' => 'Botella PET', 'contenido' => 250, 'sabor' => 'Frutos del Bosque', 'precio' => 4.00, 'minimo' => 30, 'caja' => 24],
                ]],
            ],
        ],
        [
            'categoria' => [
                'code' => 'CON',
                'name' => 'Conservas y Enlatados',
                'description' => 'Conservas, salsas y enlatados de la línea de conserva.',
            ],
            'productos' => [
                ['name' => 'Atún en Lata', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Lata', 'contenido' => 425, 'sabor' => 'En aceite', 'precio' => 21.00, 'minimo' => 18, 'caja' => 48],
                ]],
                ['name' => 'Maíz en Lata', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Lata', 'contenido' => 425, 'sabor' => 'Dulce', 'precio' => 8.50, 'minimo' => 24, 'caja' => 48],
                ]],
                ['name' => 'Salsa de Tomate', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Frasco', 'contenido' => 400, 'sabor' => 'Tradicional', 'precio' => 6.50, 'minimo' => 26, 'caja' => 24],
                ]],
                ['name' => 'Mermelada de Durazno', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Frasco', 'contenido' => 340, 'sabor' => 'Natural', 'precio' => 12.00, 'minimo' => 20, 'caja' => 24],
                ]],
            ],
        ],
        [
            'categoria' => [
                'code' => 'LAC',
                'name' => 'Lácteos y Quesos',
                'description' => 'Lácteos refrigerados y quesos de la planta.',
            ],
            'productos' => [
                ['name' => 'Yogur Natural', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Vaso', 'contenido' => 155, 'sabor' => 'Natural', 'precio' => 3.50, 'minimo' => 40, 'caja' => 30],
                    ['unidad' => 'Kilogramo', 'empaque' => 'Tacho', 'contenido' => 1, 'sabor' => 'Natural', 'precio' => 18.00, 'minimo' => 15, 'caja' => 6],
                ]],
                ['name' => 'Queso Fresco', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Bolsa', 'contenido' => 500, 'sabor' => 'Natural', 'precio' => 14.00, 'minimo' => 18, 'caja' => 12],
                ]],
                ['name' => 'Leche UAT', 'variantes' => [
                    ['unidad' => 'Mililitro', 'empaque' => 'Botella', 'contenido' => 200, 'sabor' => 'Entera', 'precio' => 2.20, 'minimo' => 48, 'caja' => 40],
                ]],
                ['name' => 'Crema de Leche', 'variantes' => [
                    ['unidad' => 'Mililitro', 'empaque' => 'Frasco', 'contenido' => 250, 'sabor' => 'Entera', 'precio' => 5.50, 'minimo' => 22, 'caja' => 24],
                ]],
            ],
        ],
        [
            'categoria' => [
                'code' => 'PAN',
                'name' => 'Panificación y Repostería',
                'description' => 'Pan de horno y galletas de la línea de panificación.',
            ],
            'productos' => [
                ['name' => 'Pan Marraqueta', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Bolsa', 'contenido' => 250, 'sabor' => null, 'precio' => 3.00, 'minimo' => 30, 'caja' => 20],
                ]],
                ['name' => 'Pan Integral', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Bolsa', 'contenido' => 400, 'sabor' => null, 'precio' => 5.00, 'minimo' => 25, 'caja' => 20],
                ]],
                ['name' => 'Galleta de Avena', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Paquete', 'contenido' => 100, 'sabor' => null, 'precio' => 4.00, 'minimo' => 30, 'caja' => 36],
                ]],
                ['name' => 'Galleta de Soda', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Paquete', 'contenido' => 90, 'sabor' => null, 'precio' => 2.80, 'minimo' => 35, 'caja' => 36],
                ]],
            ],
        ],
        [
            'categoria' => [
                'code' => 'DES',
                'name' => 'Deshidratados e Instanáneos',
                'description' => 'Granos, harinas y productos deshidratados de la planta.',
            ],
            'productos' => [
                ['name' => 'Avena Instantánea', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Bolsa', 'contenido' => 500, 'sabor' => null, 'precio' => 9.50, 'minimo' => 20, 'caja' => 12],
                ]],
                ['name' => 'Harina de Trigo', 'variantes' => [
                    ['unidad' => 'Kilogramo', 'empaque' => 'Bolsa', 'contenido' => 1, 'sabor' => null, 'precio' => 6.00, 'minimo' => 30, 'caja' => 10],
                ]],
                ['name' => 'Fideos de Trigo', 'variantes' => [
                    ['unidad' => 'Gramo', 'empaque' => 'Bolsa', 'contenido' => 500, 'sabor' => 'Espirales', 'precio' => 7.00, 'minimo' => 22, 'caja' => 12],
                    ['unidad' => 'Gramo', 'empaque' => 'Bolsa', 'contenido' => 500, 'sabor' => 'Tirabuzones', 'precio' => 7.00, 'minimo' => 20, 'caja' => 12],
                ]],
            ],
        ],
    ];

    public function run(CodigoService $codigos): void
    {
        $unidadBase = UnitMeasure::where('name', 'Unidad')->firstOrFail();
        $caja = UnitMeasure::where('name', 'Caja')->firstOrFail();

        foreach ($this->catalogo as $grupo) {
            $categoria = Category::create([
                'code' => $grupo['categoria']['code'],
                'name' => $grupo['categoria']['name'],
                'description' => $grupo['categoria']['description'],
            ]);

            foreach ($grupo['productos'] as $datos) {
                $producto = Product::create([
                    'category_id' => $categoria->id,
                    'code' => $codigos->siguienteProducto(),
                    'name' => $datos['name'],
                    'description' => null,
                    'is_active' => true,
                ]);

                foreach ($datos['variantes'] as $variante) {
                    $unidad = UnitMeasure::where('name', $variante['unidad'])->firstOrFail();

                    ProductVariant::create([
                        'product_id' => $producto->id,
                        'unit_measure_id' => $unidad->id,
                        'unidad_base_id' => $unidadBase->id,
                        'unidad_empaque_id' => $caja->id,
                        'factor_empaque' => $variante['caja'],
                        'sku' => $codigos->siguienteSku($producto),
                        'flavor' => $variante['sabor'],
                        'packaging_type' => $variante['empaque'],
                        'net_content' => $variante['contenido'],
                        'minimum_stock' => $variante['minimo'],
                        'unit_price' => $variante['precio'],
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command?->info('Categorías: '.Category::count());
        $this->command?->info('Productos: '.Product::count());
        $this->command?->info('Variantes: '.ProductVariant::count());
    }
}
