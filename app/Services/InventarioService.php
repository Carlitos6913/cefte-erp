<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\ProductVariant;
use RuntimeException;

/**
 * Motor de inventario por lotes.
 *
 * El stock vive en `lotes`. Las entradas crean (o reutilizan) un lote
 * con su fecha de caducidad; las salidas consumen lotes por FEFO
 * (First Expired, First Out). Cada operación deja su asiento en el
 * kardex (`movimientos_inventario`) referenciando el lote.
 */
class InventarioService
{
    /**
     * Registra una ENTRADA de mercadería creando (o sumando a) un lote.
     *
     * Si ya existe un lote con el mismo código y caducidad para la
     * variante, se le suma el stock en lugar de duplicarlo.
     *
     * `$cantidad` llega en unidad base. `$unidadId` es solo la unidad
     * en la que el usuario capturó la cantidad, para el kardex.
     *
     * @param  array{lote_id:int, codigo:string, fecha_caducidad:string}  $lote
     */
    public function registrarEntrada(
        ProductVariant $variante,
        int $loteId,
        float $cantidad,
        string $referencia,
        int $referenciaId,
        ?string $numero = null,
        ?int $userId = null,
        ?int $unidadId = null,
    ): MovimientoInventario {
        $lote = Lote::whereKey($loteId)->lockForUpdate()->firstOrFail();

        $anterior = (float) $lote->stock;
        $nuevo = $anterior + $cantidad;

        $lote->update(['stock' => $nuevo]);

        return MovimientoInventario::create([
            'product_variant_id' => $variante->id,
            'lote_id' => $lote->id,
            'entrada' => $cantidad,
            'salida' => 0,
            'unidad_id' => $unidadId,
            'stock_anterior' => $anterior,
            'stock_nuevo' => $nuevo,
            'referencia' => $referencia,
            'referencia_id' => $referenciaId,
            'numero' => $numero,
            'user_id' => $userId,
        ]);
    }

    /**
     * Crea (o reutiliza) el lote de una línea de recepción.
     *
     * Devuelve el lote listo para usarse en `registrarEntrada`.
     */
    public function obtenerOCrearLote(
        ProductVariant $variante,
        string $codigo,
        string $fechaCaducidad,
    ): Lote {
        $lote = Lote::where('product_variant_id', $variante->id)
            ->where('codigo', $codigo)
            ->whereDate('fecha_caducidad', $fechaCaducidad)
            ->lockForUpdate()
            ->first();

        if ($lote) {
            return $lote;
        }

        return Lote::create([
            'product_variant_id' => $variante->id,
            'codigo' => $codigo,
            'fecha_caducidad' => $fechaCaducidad,
            'cantidad_inicial' => 0,
            'stock' => 0,
            'is_active' => true,
        ]);
    }

    /**
     * Consume una SALIDA por FEFO, devolviendo una asignación por lote.
     *
     * Recorre los lotes disponibles de la variante ordenados por
     * caducidad ascendente y descuenta hasta cubrir la cantidad. La
     * suma de `cantidad` siempre es exactamente `$cantidad`; si el
     * stock total es insuficiente, lanza una excepción.
     *
     * @return array<int, array{lote_id:int, codigo:string, fecha_caducidad:string, cantidad:float, movimiento:MovimientoInventario}>
     */
    public function consumirSalidaFefo(
        ProductVariant $variante,
        float $cantidad,
        string $referencia,
        int $referenciaId,
        ?string $numero = null,
        ?int $userId = null,
        ?int $unidadId = null,
    ): array {
        $pendiente = $cantidad;

        $lotes = Lote::where('product_variant_id', $variante->id)
            ->disponibleFefo()
            ->lockForUpdate()
            ->get();

        $asignaciones = [];

        foreach ($lotes as $lote) {
            if ($pendiente <= 0) {
                break;
            }

            $disponible = (float) $lote->stock;

            if ($disponible <= 0) {
                continue;
            }

            $tomar = min($disponible, $pendiente);

            $anterior = $disponible;

            $lote->update(['stock' => $disponible - $tomar]);

            $movimiento = MovimientoInventario::create([
                'product_variant_id' => $variante->id,
                'lote_id' => $lote->id,
                'entrada' => 0,
                'salida' => $tomar,
                'unidad_id' => $unidadId,
                'stock_anterior' => $anterior,
                'stock_nuevo' => $anterior - $tomar,
                'referencia' => $referencia,
                'referencia_id' => $referenciaId,
                'numero' => $numero,
                'user_id' => $userId,
            ]);

            $asignaciones[] = [
                'lote_id' => $lote->id,
                'codigo' => $lote->codigo,
                'fecha_caducidad' => $lote->fecha_caducidad->toDateString(),
                'cantidad' => $tomar,
                'movimiento' => $movimiento,
            ];

            $pendiente -= $tomar;
        }

        if ($pendiente > 0) {
            throw new RuntimeException(
                "Stock insuficiente para la variante {$variante->sku}. Faltan {$pendiente} unidades."
            );
        }

        return $asignaciones;
    }

    /**
     * Revierte una entrada devolviendo stock a su lote.
     */
    public function revertirEntrada(
        int $loteId,
        float $cantidad,
        string $referencia,
        int $referenciaId,
        ?int $userId = null,
    ): MovimientoInventario {
        $lote = Lote::whereKey($loteId)->lockForUpdate()->firstOrFail();

        $anterior = (float) $lote->stock;
        $nuevo = $anterior - $cantidad;

        if ($nuevo < 0) {
            throw new RuntimeException(
                "No se puede revertir la entrada: el lote {$lote->codigo} quedaría en negativo."
            );
        }

        $lote->update(['stock' => $nuevo]);

        return MovimientoInventario::create([
            'product_variant_id' => $lote->product_variant_id,
            'lote_id' => $lote->id,
            'entrada' => 0,
            'salida' => $cantidad,
            'stock_anterior' => $anterior,
            'stock_nuevo' => $nuevo,
            'referencia' => $referencia,
            'referencia_id' => $referenciaId,
            'numero' => null,
            'user_id' => $userId,
        ]);
    }

    /**
     * Revierte una salida devolviendo stock a su lote.
     */
    public function revertirSalida(
        int $loteId,
        float $cantidad,
        string $referencia,
        int $referenciaId,
        ?int $userId = null,
    ): MovimientoInventario {
        $lote = Lote::whereKey($loteId)->lockForUpdate()->firstOrFail();

        $anterior = (float) $lote->stock;
        $nuevo = $anterior + $cantidad;

        $lote->update(['stock' => $nuevo]);

        return MovimientoInventario::create([
            'product_variant_id' => $lote->product_variant_id,
            'lote_id' => $lote->id,
            'entrada' => $cantidad,
            'salida' => 0,
            'stock_anterior' => $anterior,
            'stock_nuevo' => $nuevo,
            'referencia' => $referencia,
            'referencia_id' => $referenciaId,
            'numero' => null,
            'user_id' => $userId,
        ]);
    }

    /**
     * Stock total disponible de una variante (suma de sus lotes).
     */
    public function stockDisponible(int $productVariantId): float
    {
        return (float) Lote::where('product_variant_id', $productVariantId)->sum('stock');
    }
}
