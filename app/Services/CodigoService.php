<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Secuencia;
use Illuminate\Support\Facades\DB;


class CodigoService
{
    public const PREFIJO_PRODUCTO = 'PROD';

    public function siguienteProducto(): string
    {
        return $this->siguiente(self::PREFIJO_PRODUCTO);
    }

    
    public function siguiente(string $prefijo, int $largo = 6): string
    {
        return DB::transaction(function () use ($prefijo, $largo) {
            $secuencia = Secuencia::where('codigo', $prefijo)
                ->lockForUpdate()
                ->first();

            if (! $secuencia) {
                $secuencia = Secuencia::create([
                    'codigo' => $prefijo,
                    'ultimo' => 0,
                ]);
            }

            $secuencia->increment('ultimo');
            $ultimo = (int) $secuencia->refresh()->ultimo;

            return sprintf('%s-%0'.$largo.'d', $prefijo, $ultimo);
        });
    }

    
    public function previsualizarProducto(): string
    {
        return $this->previsualizar(self::PREFIJO_PRODUCTO);
    }

    public function previsualizar(string $prefijo, int $largo = 6): string
    {
        $ultimo = (int) (Secuencia::where('codigo', $prefijo)->value('ultimo') ?? 0);

        return sprintf('%s-%0'.$largo.'d', $prefijo, $ultimo + 1);
    }

    
    public function siguienteSku(Product $producto): string
    {
        return DB::transaction(function () use ($producto) {
            $bloqueado = Product::whereKey($producto->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            return $bloqueado->code.'-'.$this->ordinalDeVariante($bloqueado->id);
        });
    }

    public function previsualizarSku(Product $producto): string
    {
        return $producto->code.'-'.$this->ordinalDeVariante($producto->id);
    }

    private function ordinalDeVariante(int $productId): int
    {
        return (int) ProductVariant::where('product_id', $productId)->count() + 1;
    }
}
