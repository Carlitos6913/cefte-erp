<?php

namespace App\Services;

use App\Models\ProductVariant;
use InvalidArgumentException;

/**
 * Convierte cantidades entre la unidad base de la variante (la que
 * controla el stock) y su empaque de almacenaje (por ejemplo, la caja).
 *
 * El stock, el kardex y los saldos de despacho se expresan siempre
 * en unidad base. El empaque es solo una forma de capturar y mostrar
 * cantidades: "3 cajas" de un producto cuyo factor es 24 equivale a
 * 72 unidades.
 */
class UnidadService
{
    /**
     * Factor de conversión de la unidad indicada a unidad base.
     *
     * @param  int  $unidadId  unidad elegida por el usuario
     * @return float 1 para la unidad base, `factor_empaque` para el empaque
     *
     * @throws InvalidArgumentException si la unidad no pertenece a la variante
     */
    public function factor(ProductVariant $variante, int $unidadId): float
    {
        if ($unidadId === (int) $variante->unidad_base_id) {
            return 1.0;
        }

        $empaqueId = (int) $variante->unidad_empaque_id;
        $factor = $variante->factor_empaque === null
            ? null
            : (float) $variante->factor_empaque;

        if ($empaqueId > 0 && $factor !== null && $factor > 0 && $unidadId === $empaqueId) {
            return $factor;
        }

        $nombre = $variante->relationLoaded('unidadBase') && $variante->unidadBase
            ? $variante->unidadBase->name
            : $variante->unidad_base_id;

        throw new InvalidArgumentException(
            "La unidad seleccionada ({$unidadId}) no corresponde a la variante {$variante->sku}. "
            ."Solo se admite su unidad base ({$nombre}) o el empaque configurado."
        );
    }

    /**
     * Normaliza a unidad base la cantidad capturada por el usuario.
     */
    public function aUnidadBase(ProductVariant $variante, float $cantidad, int $unidadId): float
    {
        return round($cantidad * $this->factor($variante, $unidadId), 4);
    }

    /**
     * Expresa una cantidad en unidad base usando el empaque.
     *
     * 96 unidades con factor 24 se muestran como "4 cajas".
     */
    public function aEmpaque(ProductVariant $variante, float $cantidadBase): float
    {
        $factor = $variante->factor_empaque === null
            ? null
            : (float) $variante->factor_empaque;

        if ($factor === null || $factor <= 0) {
            return 0.0;
        }

        return round($cantidadBase / $factor, 4);
    }

    /**
     * Convierte a unidad base el precio capturado.
     *
     * Un precio de "Bs. 120 la caja" con factor 24 se guarda como
     * Bs. 5.00 por unidad, porque el catálogo siempre cotiza por unidad.
     */
    public function precioPorUnidad(ProductVariant $variante, float $precio, int $unidadId): float
    {
        return round($precio / $this->factor($variante, $unidadId), 4);
    }

    /**
     * Precio de una unidad base expresado en el empaque, para mostrarlo
     * como referencia al usuario.
     */
    public function precioPorEmpaque(ProductVariant $variante, float $precioUnidad): float
    {
        $factor = $variante->factor_empaque === null
            ? null
            : (float) $variante->factor_empaque;

        if ($factor === null || $factor <= 0) {
            return 0.0;
        }

        return round($precioUnidad * $factor, 2);
    }

    /**
     * Unidades con las que el usuario puede capturar un documento de
     * esta variante.
     *
     * @return array<int, array{id:int, name:string, symbol:string, factor:float}>
     */
    public function unidadesCapturables(ProductVariant $variante): array
    {
        if (! $variante->relationLoaded('unidadBase') || ! $variante->relationLoaded('unidadEmpaque')) {
            $variante->loadMissing(['unidadBase', 'unidadEmpaque']);
        }

        return $variante->unidadesCapturables();
    }

    /**
     * Verifica que la unidad capturada sea una de las permitidas para
     * todas las variantes de un documento. Devuelve un mapa
     * variante_id => factor para evitar repetir la consulta.
     *
     * @param  array<int, array{product_variant_id:int, unidad_id:int}>  $lineas
     * @return array<int, float>
     *
     * @throws InvalidArgumentException
     */
    public function factoresPorLinea(array $lineas): array
    {
        $ids = array_values(array_unique(array_column($lineas, 'product_variant_id')));

        $variantes = ProductVariant::with(['unidadBase', 'unidadEmpaque'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        $factores = [];

        foreach ($lineas as $linea) {
            $variante = $variantes->get($linea['product_variant_id']);

            if (! $variante) {
                throw new InvalidArgumentException(
                    "La variante {$linea['product_variant_id']} no existe."
                );
            }

            $factores[$linea['product_variant_id']] = $this->factor($variante, $linea['unidad_id']);
        }

        return $factores;
    }
}
