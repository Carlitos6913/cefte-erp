<?php

namespace App\Services;

use App\Models\Correlativo;

class NumeradorService
{
    /**
     * Genera el siguiente número correlativo para un documento.
     * El conteo es por año y se protege con bloqueo de fila
     * (LOCK FOR UPDATE) y un índice único (codigo, anio).
     *
     * Formato: PREFIJO-AAAA-000001
     */
    public function siguiente(string $codigo, ?int $anio = null): string
    {
        $anio = $anio ?? (int) now()->format('Y');

        $fila = Correlativo::where('codigo', $codigo)
            ->where('anio', $anio)
            ->lockForUpdate()
            ->first();

        if (! $fila) {
            $fila = Correlativo::create([
                'codigo' => $codigo,
                'anio' => $anio,
                'ultimo' => 0,
            ]);
        }

        $fila->increment('ultimo');
        $ultimo = $fila->refresh()->ultimo;

        return sprintf('%s-%d-%06d', strtoupper($codigo), $anio, $ultimo);
    }
}
