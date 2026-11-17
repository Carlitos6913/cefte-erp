<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModuloMiddleware
{
    public function handle(
        Request $request,
        Closure $next,
        ...$modulos
    ): Response {

        if (! $request->user()) {
            abort(403);
        }

        $rol = $request->user()->roles->first();

        if (! $rol) {
            abort(403);
        }

        // Si el rol no tiene modulos asignados, tiene acceso total
        // (rol predefinido: Administrador)
        if (empty($rol->modulos)) {
            return $next($request);
        }

        $modulosRol = is_array($rol->modulos)
            ? $rol->modulos
            : json_decode($rol->modulos, true) ?? [];

        foreach ($modulos as $modulo) {
            if (in_array($modulo, $modulosRol)) {
                return $next($request);
            }
        }

        abort(403);
    }
}
