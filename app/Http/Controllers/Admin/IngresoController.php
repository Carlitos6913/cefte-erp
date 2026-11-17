<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Employee;
use App\Models\Ingreso;
use App\Models\ProductVariant;
use App\Services\InventarioService;
use App\Services\NumeradorService;
use App\Services\UnidadService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use InvalidArgumentException;

class IngresoController extends Controller
{
    protected InventarioService $inventario;

    protected NumeradorService $numerador;

    protected UnidadService $unidades;

    public function __construct(
        InventarioService $inventario,
        NumeradorService $numerador,
        UnidadService $unidades,
    ) {
        $this->inventario = $inventario;
        $this->numerador = $numerador;
        $this->unidades = $unidades;
    }

    /**
     * Personal habilitado para recibir la mercadería.
     */
    private function encargados(): Collection
    {
        return Employee::where('is_active', true)
            ->orderBy('first_surname')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'first_surname', 'second_surname', 'identity_card']);
    }

    /**
     * Variantes que el usuario puede recibir, con las unidades en las
     * que puede capturar la cantidad (unidad base y, si tiene, caja).
     *
     * @return Collection<int, ProductVariant>
     */
    private function productosRecepcionables(): Collection
    {
        return ProductVariant::where('is_active', true)
            ->with(['unidadBase:id,name,symbol', 'unidadEmpaque:id,name,symbol'])
            ->with('unitMeasure:id,name,symbol')
            ->with('product:id,name,code,category_id,image_path')
            ->orderBy('sku')
            ->get([
                'id',
                'product_id',
                'unit_measure_id',
                'unidad_base_id',
                'unidad_empaque_id',
                'factor_empaque',
                'packaging_type',
                'net_content',
                'sku',
                'flavor',
                'unit_price',
            ])
            ->each(function (ProductVariant $variante) {
                $variante->setAttribute(
                    'unidades',
                    $variante->unidadesCapturables()
                );
            });
    }

    public function index()
    {
        $ingresos = Ingreso::with([
            'usuario',
            'encargado',
            'detalles.unidad:id,name,symbol',
            'detalles.lote:id,codigo,fecha_caducidad',
            'detalles.productVariant.product',
            'detalles.productVariant.product.category:id,name',
            'detalles.productVariant.unitMeasure',
            'detalles.productVariant.unidadBase:id,name,symbol',
        ])
            ->withCount('detalles')
            ->latest('fecha')
            ->latest('id')
            ->paginate(10);

        $productosStock = ProductVariant::where('is_active', true)
            ->conStock()
            ->with([
                'product:id,name,code,category_id,image_path',
                'product.category:id,name',
                'unidadBase:id,name,symbol',
            ])
            ->orderBy('sku')
            ->get()
            ->map(fn (ProductVariant $variante) => [
                'id' => $variante->id,
                'nombre' => $variante->product?->name ?? $variante->sku,
                'codigo' => $variante->sku,
                'sabor' => $variante->flavor,
                'categoria' => $variante->product?->category?->name,
                'imagen' => $variante->product?->image_url,
                'empaque' => $variante->packaging_type,
                'contenido' => $variante->net_content !== null
                    ? (float) $variante->net_content
                    : null,
                'stock' => $variante->stock,
                'minimo' => (float) $variante->minimum_stock,
                'unidad' => $variante->unidadBase?->symbol,
            ]);

        return Inertia::render('Inventario/Ingresos/Index', [
            'ingresos' => $ingresos,
            'productosStock' => $productosStock,
        ]);
    }

    public function create()
    {
        return Inertia::render('Inventario/Ingresos/Create', [
            'categorias' => Category::orderBy('name')->get(['id', 'name']),
            'encargados' => $this->encargados(),
            'productos' => $this->productosRecepcionables(),
        ]);
    }

    /**
     * Convierte cada línea a unidad base.
     *
     * El usuario puede capturar "3 cajas"; internamente se guarda
     * `cantidad` = 3 x factor_empaque, que es lo que suma al stock y
     * al kardex, y `cantidad_original` = 3 con su `unidad_id` para
     * mostrar el documento tal como se registró.
     *
     * @param  array<int, array<string, mixed>>  $detalles
     * @return array<int, array<string, mixed>>
     */
    private function normalizarDetalles(array $detalles): array
    {
        $factores = $this->unidades->factoresPorLinea($detalles);

        return array_map(function (array $detalle) use ($factores) {
            $factor = $factores[$detalle['product_variant_id']];

            $cantidadBase = (int) round($detalle['cantidad'] * $factor);

            return [
                'product_variant_id' => $detalle['product_variant_id'],
                'lote' => $detalle['lote'],
                'fecha_caducidad' => $detalle['fecha_caducidad'],
                'cantidad' => $cantidadBase,
                'unidad_id' => $detalle['unidad_id'],
                'cantidad_original' => $detalle['cantidad'],
                'precio_unitario' => round($detalle['precio_unitario'] / $factor, 2),
                'subtotal' => round($cantidadBase * round($detalle['precio_unitario'] / $factor, 2), 2),
            ];
        }, $detalles);
    }

    /**
     * Reglas de validación de las líneas de detalle del ingreso.
     *
     * @return array<string, array<int, string>>
     */
    private function reglasDetalles(): array
    {
        return [
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.product_variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'detalles.*.lote' => ['required', 'string', 'max:50'],
            'detalles.*.fecha_caducidad' => ['required', 'date'],
            'detalles.*.unidad_id' => ['required', 'integer', 'exists:unit_measures,id'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.0001'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'fecha' => [
                'required',
                'date',
            ],

            'encargado_id' => [
                'required',
                'integer',
                'exists:employees,id',
            ],

            'observaciones' => [
                'nullable',
                'string',
            ],
        ], $this->reglasDetalles()));

        try {
            $lineas = $this->normalizarDetalles($validated['detalles']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['detalles' => $e->getMessage()])->withInput();
        }

        DB::transaction(function () use ($validated, $lineas) {

            $numero = $this->numerador->siguiente('ING');

            $total = collect($lineas)->sum('subtotal');

            $ingreso = Ingreso::create([
                'numero' => $numero,
                'fecha' => $validated['fecha'],
                'encargado_id' => $validated['encargado_id'],
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
                'estado' => 'activo',
                'user_id' => Auth::id(),
            ]);

            foreach ($lineas as $linea) {

                $variante = ProductVariant::findOrFail($linea['product_variant_id']);

                $lote = $this->inventario->obtenerOCrearLote(
                    $variante,
                    $linea['lote'],
                    $linea['fecha_caducidad'],
                );

                $ingreso->detalles()->create([
                    'product_variant_id' => $linea['product_variant_id'],
                    'lote_id' => $lote->id,
                    'cantidad' => $linea['cantidad'],
                    'unidad_id' => $linea['unidad_id'],
                    'cantidad_original' => $linea['cantidad_original'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => $linea['subtotal'],
                ]);

                $this->inventario->registrarEntrada(
                    $variante,
                    $lote->id,
                    $linea['cantidad'],
                    'ingreso',
                    $ingreso->id,
                    $numero,
                    Auth::id(),
                    $linea['unidad_id'],
                );
            }
        });

        return redirect()
            ->route('ingresos.index')
            ->with('success', 'Ingreso registrado correctamente.');
    }

    public function show(string $id)
    {
        $ingreso = Ingreso::with([
            'usuario',
            'encargado',
            'detalles.unidad:id,name,symbol',
            'detalles.lote:id,codigo,fecha_caducidad',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
            'detalles.productVariant.unidadBase:id,name,symbol',
        ])->findOrFail($id);

        return Inertia::render('Inventario/Ingresos/Show', [
            'ingreso' => $ingreso,
        ]);
    }

    public function edit(string $id)
    {
        $ingreso = Ingreso::with([
            'encargado',
            'detalles.unidad:id,name,symbol',
            'detalles.lote:id,codigo,fecha_caducidad',
            'detalles.productVariant.product',
            'detalles.productVariant.unitMeasure',
            'detalles.productVariant.unidadBase:id,name,symbol',
        ])
            ->findOrFail($id);

        if ($ingreso->estado === 'anulado') {
            return redirect()
                ->route('ingresos.show', $ingreso->id)
                ->with('error', 'No se puede editar un ingreso anulado.');
        }

        /*
         * Se cargan los empleados activos para el selector, pero si el
         * encargado actual quedó inactivo se agrega igual para que el
         * formulario lo muestre y lo conserve.
         */
        $encargados = $this->encargados();

        if ($ingreso->encargado && ! $encargados->contains($ingreso->encargado)) {
            $encargados->push($ingreso->encargado);
        }

        return Inertia::render('Inventario/Ingresos/Edit', [
            'ingreso' => $ingreso,
            'categorias' => Category::orderBy('name')->get(['id', 'name']),
            'encargados' => $encargados,
            'productos' => $this->productosRecepcionables(),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $ingreso = Ingreso::with('detalles')
            ->findOrFail($id);

        if ($ingreso->estado === 'anulado') {
            return back()->with(
                'error',
                'No se puede modificar un ingreso anulado.'
            );
        }

        $validated = $request->validate(array_merge([
            'fecha' => [
                'required',
                'date',
            ],

            'encargado_id' => [
                'required',
                'integer',
                'exists:employees,id',
            ],

            'observaciones' => [
                'nullable',
                'string',
            ],
        ], $this->reglasDetalles()));

        try {
            $lineas = $this->normalizarDetalles($validated['detalles']);
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['detalles' => $e->getMessage()])->withInput();
        }

        DB::transaction(function () use ($ingreso, $validated, $lineas) {

            /*
             * 1. Revertir el stock generado por
             *    el ingreso anterior. El detalle guarda la
             *    cantidad ya normalizada a unidad base.
             */
            foreach ($ingreso->detalles as $detalle) {
                if ($detalle->lote_id === null) {
                    continue;
                }

                $this->inventario->revertirEntrada(
                    $detalle->lote_id,
                    $detalle->cantidad,
                    'ingreso',
                    $ingreso->id,
                    Auth::id(),
                );
            }

            /*
             * 2. Eliminar los detalles anteriores.
             */
            $ingreso->detalles()->delete();

            /*
             * 3. Calcular el nuevo total.
             */
            $total = collect($lineas)->sum('subtotal');

            /*
             * 4. Actualizar información general.
             */
            $ingreso->update([
                'fecha' => $validated['fecha'],
                'encargado_id' => $validated['encargado_id'],
                'observaciones' => $validated['observaciones'] ?? null,
                'total' => $total,
            ]);

            /*
             * 5. Crear los nuevos detalles
             *    y aplicar el nuevo stock.
             */
            foreach ($lineas as $linea) {

                $variante = ProductVariant::findOrFail($linea['product_variant_id']);

                $lote = $this->inventario->obtenerOCrearLote(
                    $variante,
                    $linea['lote'],
                    $linea['fecha_caducidad'],
                );

                $ingreso->detalles()->create([
                    'product_variant_id' => $linea['product_variant_id'],
                    'lote_id' => $lote->id,
                    'cantidad' => $linea['cantidad'],
                    'unidad_id' => $linea['unidad_id'],
                    'cantidad_original' => $linea['cantidad_original'],
                    'precio_unitario' => $linea['precio_unitario'],
                    'subtotal' => $linea['subtotal'],
                ]);

                $this->inventario->registrarEntrada(
                    $variante,
                    $lote->id,
                    $linea['cantidad'],
                    'ingreso',
                    $ingreso->id,
                    $ingreso->numero,
                    Auth::id(),
                    $linea['unidad_id'],
                );
            }
        });

        return redirect()
            ->route('ingresos.show', $ingreso->id)
            ->with('success', 'Ingreso actualizado correctamente.');
    }

    public function destroy(string $id)
    {
        $ingreso = Ingreso::with('detalles')
            ->findOrFail($id);

        if ($ingreso->estado === 'anulado') {
            return back()->with(
                'error',
                'El ingreso ya se encuentra anulado.'
            );
        }

        DB::transaction(function () use ($ingreso) {

            /*
             * Revertir el stock.
             */
            foreach ($ingreso->detalles as $detalle) {
                if ($detalle->lote_id === null) {
                    continue;
                }

                $this->inventario->revertirEntrada(
                    $detalle->lote_id,
                    $detalle->cantidad,
                    'ingreso',
                    $ingreso->id,
                    Auth::id(),
                );
            }

            /*
             * No eliminamos el registro.
             * Lo marcamos como anulado.
             */
            $ingreso->update([
                'estado' => 'anulado',
            ]);
        });

        return redirect()
            ->route('ingresos.index')
            ->with('success', 'Ingreso anulado correctamente.');
    }
}
