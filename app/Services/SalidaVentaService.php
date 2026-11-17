<?php

namespace App\Services;

use App\Models\Salida;
use App\Models\SalidaDetalle;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Gestiona el saldo autorizado para venta.
 *
 * Únicamente las salidas con motivo 'venta' y estado 'activo'
 * disponibilizan mercancía para ser vendida. Las ventas consumen
 * ese saldo (primero en entrar, primero en salir) sin volver a
 * descontar el stock físico, que ya fue movido por la salida.
 */
class SalidaVentaService
{
    /**
     * Cantidad total de unidades autorizadas sin vender para una variante.
     */
    public function saldoDisponible(int $productVariantId): int
    {
        return (int) DB::table('salida_detalles as sd')
            ->join('salidas as s', 's.id', '=', 'sd.salida_id')
            ->where('s.motivo', Salida::MOTIVO_VENTA)
            ->where('s.estado', 'activo')
            ->where('sd.product_variant_id', $productVariantId)
            ->sum(DB::raw('sd.cantidad - sd.cantidad_vendida'));
    }

    /**
     * Saldo autorizado sin vender por cada variante.
     *
     * @return array<int, int> map variante_id => saldo
     */
    public function saldosPorVariante(): array
    {
        return DB::table('salida_detalles as sd')
            ->join('salidas as s', 's.id', '=', 'sd.salida_id')
            ->where('s.motivo', Salida::MOTIVO_VENTA)
            ->where('s.estado', 'activo')
            ->select('sd.product_variant_id', DB::raw('SUM(sd.cantidad - sd.cantidad_vendida) as saldo'))
            ->groupBy('sd.product_variant_id')
            ->having('saldo', '>', 0)
            ->orderBy('sd.product_variant_id')
            ->pluck('saldo', 'product_variant_id')
            ->map(fn ($saldo) => (int) $saldo)
            ->all();
    }

    /**
     * Consume $cantidad del saldo autorizado de la variante,
     * asignando las líneas de salida disponibles por FEFO (primero
     * el lote que caduca antes). Registra la relación en el pivote.
     */
    public function consumir(int $productVariantId, int $cantidad, int $ventaDetalleId): void
    {
        $saldoRestante = $cantidad;

        $detalles = SalidaDetalle::query()
            ->join('salidas as s', 's.id', '=', 'salida_detalles.salida_id')
            ->leftJoin('lotes as l', 'l.id', '=', 'salida_detalles.lote_id')
            ->where('s.motivo', Salida::MOTIVO_VENTA)
            ->where('s.estado', 'activo')
            ->where('salida_detalles.product_variant_id', $productVariantId)
            ->whereColumn('salida_detalles.cantidad_vendida', '<', 'salida_detalles.cantidad')
            ->orderByRaw('l.fecha_caducidad IS NULL')
            ->orderBy('l.fecha_caducidad')
            ->orderBy('s.fecha')
            ->orderBy('s.id')
            ->select('salida_detalles.*')
            ->lockForUpdate()
            ->get();

        foreach ($detalles as $detalle) {
            if ($saldoRestante <= 0) {
                break;
            }

            $disponible = $detalle->cantidad - $detalle->cantidad_vendida;

            $tomar = min($disponible, $saldoRestante);

            $detalle->increment('cantidad_vendida', $tomar);

            DB::table('salida_detalle_venta_detalle')->insert([
                'salida_detalle_id' => $detalle->id,
                'venta_detalle_id' => $ventaDetalleId,
                'cantidad' => $tomar,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $saldoRestante -= $tomar;
        }

        if ($saldoRestante > 0) {
            throw new RuntimeException(
                "Saldo autorizado insuficiente para la variante {$productVariantId}."
            );
        }
    }

    /**
     * Devuelve el saldo consumido por un detalle de venta a sus lotes
     * de salida (usado al editar o anular una venta).
     */
    public function liberar(int $ventaDetalleId): void
    {
        $consumos = DB::table('salida_detalle_venta_detalle')
            ->where('venta_detalle_id', $ventaDetalleId)
            ->get();

        foreach ($consumos as $consumo) {
            SalidaDetalle::whereKey($consumo->salida_detalle_id)
                ->lockForUpdate()
                ->decrement('cantidad_vendida', $consumo->cantidad);
        }

        DB::table('salida_detalle_venta_detalle')
            ->where('venta_detalle_id', $ventaDetalleId)
            ->delete();
    }

    /**
     * Devuelve todo el saldo consumido por los detalles de una venta.
     */
    public function liberarVenta(int $ventaId): void
    {
        $detalles = DB::table('venta_detalles')
            ->where('venta_id', $ventaId)
            ->pluck('id');

        foreach ($detalles as $ventaDetalleId) {
            $this->liberar((int) $ventaDetalleId);
        }
    }

    /**
     * Verifica si una salida tiene al menos un detalle vendido.
     */
    public function salidaTieneVendidos(int $salidaId): bool
    {
        return SalidaDetalle::where('salida_id', $salidaId)
            ->where('cantidad_vendida', '>', 0)
            ->exists();
    }
}
