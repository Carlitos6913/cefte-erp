<?php

namespace App\Services;

use App\Models\MovimientoInventario;
use App\Models\ProductVariant;
use RuntimeException;

class InventarioService
{
    /**
     * Aplica un movimiento de stock sobre la variante dentro de una
     * transacción, bloqueando la fila (SELECT ... FOR UPDATE) para
     * evitar pérdidas de stock ante concurrencia, y registra el
     * asiento en el kardex (movimientos_inventario).
     *
     * @param  'entrada'|'salida'  $movimiento  dirección del movimiento de stock
     * @param  'ingreso'|'salida'  $referencia  tipo de documento que origina el movimiento
     */
    public function registrarMovimiento(
        int $productVariantId,
        string $movimiento,
        float $cantidad,
        string $referencia,
        int $referenciaId,
        ?string $numero,
        ?int $userId = null,
    ): MovimientoInventario {
        $variante = ProductVariant::whereKey($productVariantId)
            ->lockForUpdate()
            ->firstOrFail();

        $anterior = (float) $variante->stock;

        $nuevo = $movimiento === 'entrada'
            ? $anterior + $cantidad
            : $anterior - $cantidad;

        if ($nuevo < 0) {
            throw new RuntimeException(
                "Stock insuficiente para la variante {$variante->sku}. Disponible: {$anterior}."
            );
        }

        $variante->update(['stock' => $nuevo]);

        return MovimientoInventario::create([
            'product_variant_id' => $productVariantId,
            'entrada' => $movimiento === 'entrada' ? $cantidad : 0,
            'salida' => $movimiento === 'salida' ? $cantidad : 0,
            'stock_anterior' => $anterior,
            'stock_nuevo' => $nuevo,
            'referencia' => $referencia,
            'referencia_id' => $referenciaId,
            'numero' => $numero,
            'user_id' => $userId,
        ]);
    }

    /**
     * Revierte un movimiento registrado (edición/anulación de documento).
     */
    public function revertir(int $productVariantId, float $cantidad, string $referencia, int $referenciaId, ?int $userId = null): MovimientoInventario
    {
        $movimiento = $referencia === 'ingreso' ? 'salida' : 'entrada';

        return $this->registrarMovimiento(
            $productVariantId,
            $movimiento,
            $cantidad,
            $referencia,
            $referenciaId,
            null,
            $userId,
        );
    }
}
