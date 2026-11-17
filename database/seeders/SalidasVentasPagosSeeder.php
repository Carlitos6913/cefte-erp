<?php

namespace Database\Seeders;

use App\Models\Cliente;
use App\Models\Pago;
use App\Models\ProductVariant;
use App\Models\Salida;
use App\Models\SalidaDetalle;
use App\Models\User;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\InventarioService;
use App\Services\NumeradorService;
use App\Services\SalidaVentaService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalidasVentasPagosSeeder extends Seeder
{
    /**
     * Crea el circuito comercial completo respetando las reglas del
     * sistema: la salida descuenta el stock físico y es lo único que
     * autoriza la venta; la venta solo consume ese saldo autorizado.
     */
    public function run(
        NumeradorService $numerador,
        InventarioService $inventario,
        SalidaVentaService $salidaVenta,
    ): void {
        $almacenero = User::role('Almacenero')->first() ?? User::first();
        $vendedor = User::role('Vendedor')->first() ?? $almacenero;
        $clientes = Cliente::where('activo', true)->orderBy('id')->get();
        $variantes = ProductVariant::where('is_active', true)
            ->with('product')
            ->orderBy('id')
            ->get();

        if ($clientes->isEmpty() || $variantes->isEmpty()) {
            $this->command?->warn('Faltan clientes o variantes para el circuito comercial.');

            return;
        }

        $inicio = now()->subDays(20)->startOfDay();
        $posicion = 0;

        for ($dia = 0; $dia < 12; $dia++) {
            $this->crearSalida(
                $numerador, $inventario, $almacenero,
                $variantes, $dia, $inicio, $posicion
            );

            $posicion += 3 + ($dia % 3);

            $this->crearVenta(
                $numerador, $salidaVenta, $vendedor,
                $clientes, $dia, $inicio
            );
        }

        $this->command?->info('Salidas creadas: '.Salida::count());
        $this->command?->info('Ventas creadas: '.Venta::count());
        $this->command?->info('Pagos creados: '.Pago::count());
    }

    private function crearSalida(
        NumeradorService $numerador,
        InventarioService $inventario,
        User $usuario,
        $variantes,
        int $dia,
        $inicio,
        int $posicion,
    ): void {
        $conStock = $variantes
            ->filter(fn ($v) => (float) $v->stock > 10)
            ->values();

        if ($conStock->isEmpty()) {
            return;
        }

        $total = $conStock->count();
        $seleccionadas = collect();
        $elegidos = [];
        $restante = [];

        foreach ($conStock as $variante) {
            $restante[$variante->id] = (int) floor((float) $variante->stock);
        }

        for ($linea = 0; $seleccionadas->count() < 2 + ($dia % 3) && $linea < $total; $linea++) {
            $candidata = $conStock[($posicion + $linea * 5) % $total];

            if (in_array($candidata->id, $elegidos, true)) {
                continue;
            }

            $elegidos[] = $candidata->id;
            $seleccionadas->push($candidata);
        }

        if ($seleccionadas->isEmpty()) {
            return;
        }

        DB::transaction(function () use (
            $seleccionadas, &$restante, $numerador, $inventario, $usuario, $dia, $inicio
        ) {
            $numero = $numerador->siguiente('SAL');
            $esVenta = $dia % 4 !== 3;

            $salida = Salida::create([
                'numero' => $numero,
                'fecha' => $inicio->copy()->addDays($dia)->toDateString(),
                'motivo' => $esVenta ? Salida::MOTIVO_VENTA : 'merma',
                'observaciones' => $esVenta ? null : 'Merma por producto cercano a vencer.',
                'total' => 0,
                'estado' => 'activo',
                'user_id' => $usuario->id,
            ]);

            $total = 0;

            foreach ($seleccionadas as $variante) {
                $disponible = $restante[$variante->id] ?? 0;
                $cantidad = max(1, min(12 + (($dia * 7) % 20), (int) floor($disponible / 2)));
                $restante[$variante->id] = $disponible - $cantidad;

                // El despacho sale por FEFO: puede repartirse en una
                // línea por lote.
                $asignaciones = $inventario->consumirSalidaFefo(
                    $variante,
                    $cantidad,
                    'salida',
                    $salida->id,
                    $numero,
                    $usuario->id,
                );

                foreach ($asignaciones as $asignacion) {
                    $subtotal = round($asignacion['cantidad'] * (float) $variante->unit_price, 2);
                    $total += $subtotal;

                    SalidaDetalle::create([
                        'salida_id' => $salida->id,
                        'product_variant_id' => $variante->id,
                        'lote_id' => $asignacion['lote_id'],
                        'cantidad' => $asignacion['cantidad'],
                        'precio_unitario' => $variante->unit_price,
                        'subtotal' => $subtotal,
                        'cantidad_vendida' => 0,
                    ]);
                }
            }

            $salida->update(['total' => round($total, 2)]);
        });
    }

    private function crearVenta(
        NumeradorService $numerador,
        SalidaVentaService $salidaVenta,
        User $usuario,
        $clientes,
        int $dia,
        $inicio,
    ): void {
        $saldos = $salidaVenta->saldosPorVariante();

        $disponibles = collect($saldos)
            ->filter(fn ($saldo) => $saldo > 0)
            ->keys()
            ->map(fn ($id) => ProductVariant::with('product')->find($id))
            ->filter()
            ->values();

        if ($disponibles->isEmpty()) {
            return;
        }

        $total = $disponibles->count();
        $cantidadLineas = min(1 + ($dia % 3), $total);
        $seleccionadas = collect();
        $elegidos = [];

        for ($linea = 0; $seleccionadas->count() < $cantidadLineas && $linea < $total; $linea++) {
            $candidata = $disponibles[($dia * 3 + $linea * 7) % $total];

            if (in_array($candidata->id, $elegidos, true)) {
                continue;
            }

            $elegidos[] = $candidata->id;
            $seleccionadas->push($candidata);
        }

        if ($seleccionadas->isEmpty()) {
            return;
        }

        $detalles = $seleccionadas->map(function ($variante) use ($saldos) {
            $cantidad = max(1, min(5 + ($variante->id % 10), (int) $saldos[$variante->id]));

            return [
                'product_variant_id' => $variante->id,
                'cantidad' => $cantidad,
                'precio_unitario' => (float) $variante->unit_price,
            ];
        })->values();

        $aCredito = $dia % 3 !== 2;
        $cliente = $aCredito
            ? $clientes[$dia % $clientes->count()]
            : null;

        try {
            DB::transaction(function () use (
                $detalles, $numerador, $salidaVenta, $usuario, $cliente, $aCredito, $dia, $inicio
            ) {
                $numero = $numerador->siguiente('VEN');

                $total = round($detalles->sum(
                    fn ($d) => $d['cantidad'] * $d['precio_unitario']
                ), 2);

                $venta = Venta::create([
                    'numero' => $numero,
                    'fecha' => $inicio->copy()->addDays($dia)->toDateString(),
                    'cliente_id' => $cliente?->id,
                    'cliente' => $cliente?->nombre,
                    'observaciones' => null,
                    'total' => $total,
                    'estado' => 'activo',
                    'pago_tipo' => $aCredito ? 'credito' : 'contado',
                    'saldo_pendiente' => $aCredito ? $total : 0,
                    'user_id' => $usuario->id,
                ]);

                foreach ($detalles as $detalle) {
                    $ventaDetalle = VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'product_variant_id' => $detalle['product_variant_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $detalle['precio_unitario'],
                        'subtotal' => round(
                            $detalle['cantidad'] * $detalle['precio_unitario'],
                            2
                        ),
                    ]);

                    $salidaVenta->consumir(
                        $detalle['product_variant_id'],
                        $detalle['cantidad'],
                        $ventaDetalle->id,
                    );
                }

                if (! $aCredito) {
                    return;
                }

                $this->registrarPagosParciales($venta, $usuario, $dia);
            });
        } catch (RuntimeException $e) {
            $this->command?->warn('Venta omitida: '.$e->getMessage());
        }
    }

    private function registrarPagosParciales(Venta $venta, User $usuario, int $dia): void
    {
        if ($dia % 2 !== 0) {
            return;
        }

        $monto = round((float) $venta->total / 2, 2);

        if ($monto <= 0) {
            return;
        }

        $metodos = ['efectivo', 'transferencia', 'qr'];

        Pago::create([
            'venta_id' => $venta->id,
            'fecha' => $venta->fecha->copy()->addDays(1)->toDateString(),
            'monto' => $monto,
            'metodo' => $metodos[$dia % count($metodos)],
            'observaciones' => 'Pago parcial a cuenta.',
            'user_id' => $usuario->id,
        ]);

        $venta->update([
            'saldo_pendiente' => max(
                0,
                round((float) $venta->total - $monto, 2)
            ),
        ]);
    }
}
