<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Ingreso;
use App\Models\IngresoDetalle;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\InventarioService;
use App\Services\NumeradorService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IngresosSeeder extends Seeder
{
    /**
     * Treinta ingresos de mercadería. Cada uno mueve el stock y deja
     * su asiento en el Kardex a través de InventarioService, igual que
     * lo hace la aplicación.
     */
    public function run(NumeradorService $numerador, InventarioService $inventario): void
    {
        $almacenero = User::role('Almacenero')->first() ?? User::first();
        $variantes = ProductVariant::with('product')->orderBy('id')->get();

        // El responsable de recibir la mercadería se elige entre los
        // empleados activos de almacén.
        $encargados = Employee::where('is_active', true)
            ->orderBy('id')
            ->get(['id']);

        if ($variantes->isEmpty() || $encargados->isEmpty()) {
            $this->command?->warn('Faltan variantes o empleados para registrar ingresos.');

            return;
        }

        $inicio = now()->subDays(45)->startOfDay();
        $posicion = 0;
        $totalVariantes = $variantes->count();
        $totalEncargados = $encargados->count();

        for ($dia = 0; $dia < 30; $dia++) {
            $cantidadLineas = 1 + ($dia % 3);

            $seleccionadas = collect();

            for ($linea = 0; $linea < $cantidadLineas; $linea++) {
                $seleccionadas->push($variantes[$posicion]);
                $posicion = ($posicion + 7) % $totalVariantes;
            }

            $encargado = $encargados[$dia % $totalEncargados];

            DB::transaction(function () use (
                $seleccionadas, $encargado, $numerador, $inventario, $almacenero, $dia, $inicio
            ) {
                $numero = $numerador->siguiente('ING');

                $ingreso = Ingreso::create([
                    'numero' => $numero,
                    'fecha' => $inicio->copy()->addDays($dia)->toDateString(),
                    'encargado_id' => $encargado->id,
                    'observaciones' => null,
                    'total' => 0,
                    'estado' => 'activo',
                    'user_id' => $almacenero->id,
                ]);

                $total = 0;

                foreach ($seleccionadas as $variante) {
                    $cantidad = ((int) $variante->minimum_stock) * 2
                        + 15
                        + (($dia * 3) % 20);
                    $costo = round($variante->unit_price * 0.72, 2);
                    $subtotal = round($cantidad * $costo, 2);

                    // Cada línea de recepción entra con su propio lote y
                    // caducidad, base del control FEFO.
                    $codigoLote = 'L'.str_pad((string) ($dia + 1), 3, '0', STR_PAD_LEFT);
                    $fechaCaducidad = $inicio->copy()
                        ->addDays($dia)
                        ->addMonths(6)
                        ->toDateString();

                    $lote = $inventario->obtenerOCrearLote(
                        $variante,
                        $codigoLote,
                        $fechaCaducidad,
                    );

                    IngresoDetalle::create([
                        'ingreso_id' => $ingreso->id,
                        'product_variant_id' => $variante->id,
                        'lote_id' => $lote->id,
                        'cantidad' => $cantidad,
                        'precio_unitario' => $costo,
                        'subtotal' => $subtotal,
                    ]);

                    $inventario->registrarEntrada(
                        $variante,
                        $lote->id,
                        $cantidad,
                        'ingreso',
                        $ingreso->id,
                        $numero,
                        $almacenero->id,
                    );

                    $total += $subtotal;
                }

                $ingreso->update(['total' => round($total, 2)]);
            });
        }

        $this->command?->info('Ingresos creados: '.Ingreso::count());
        $this->command?->info('Líneas de ingreso: '.IngresoDetalle::count());
    }
}
