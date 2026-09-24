<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClienteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\InformesController;
use App\Http\Controllers\Admin\IngresoController;
use App\Http\Controllers\Admin\PagoController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\SalidaController;
use App\Http\Controllers\Admin\UnitMeasureController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VentaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // =====================================================
    // USUARIOS
    // =====================================================

    Route::get('/usuarios', [UserController::class, 'index'])
        ->middleware('modulo:usuarios')
        ->name('usuarios.index');

    Route::middleware('modulo:usuarios')->group(function () {

        Route::get('/usuarios/create', [UserController::class, 'create'])
            ->name('usuarios.create');

        Route::post('/usuarios', [UserController::class, 'store'])
            ->name('usuarios.store');

        Route::get('/usuarios/{usuario}/edit', [UserController::class, 'edit'])
            ->name('usuarios.edit');

        Route::put('/usuarios/{usuario}', [UserController::class, 'update'])
            ->name('usuarios.update');

        Route::delete('/usuarios/{usuario}', [UserController::class, 'destroy'])
            ->name('usuarios.destroy');

        Route::post('/usuarios/{usuario}/activar', [UserController::class, 'activar'])
            ->name('usuarios.activar');
    });

    // =====================================================
    // EMPLEADOS
    // =====================================================

    Route::middleware('modulo:empleados')->group(function () {
        Route::resource('empleados', EmployeeController::class)
            ->except(['show']);

        Route::post('/empleados/{empleado}/activar', [EmployeeController::class, 'activar'])
            ->name('empleados.activar');

        Route::get('/empleados/{empleado}/usuario', [EmployeeController::class, 'usuario'])
            ->name('empleados.usuario');

        Route::post('/empleados/{empleado}/usuario', [EmployeeController::class, 'asignarUsuario'])
            ->name('empleados.usuario.store');
    });

    // =====================================================
    // CATEGORÍAS
    // =====================================================

    Route::middleware('modulo:categorias')->group(function () {
        Route::resource('categorias', CategoryController::class)
            ->except(['show']);
    });

    // =====================================================
    // UNIDADES DE MEDIDA
    // =====================================================

    Route::middleware('modulo:unidades')->group(function () {
        Route::resource('unidades', UnitMeasureController::class)
            ->except(['show']);
    });

    // =====================================================
    // PRODUCTOS
    // =====================================================

    Route::middleware('modulo:productos')->group(function () {
        Route::resource('productos', ProductController::class)
            ->except(['show']);

        Route::post('/productos/{producto}/activar', [ProductController::class, 'activar'])
            ->name('productos.activar');
    });

    // =====================================================
    // VARIANTES DE PRODUCTO
    // =====================================================

    Route::middleware('modulo:variantes')->group(function () {
        Route::resource('variantes', ProductVariantController::class)
            ->except(['show']);

        Route::post('/variantes/{variante}/activar', [ProductVariantController::class, 'activar'])
            ->name('variantes.activar');
    });

    // =====================================================
    // INGRESOS
    // =====================================================

    Route::middleware('modulo:ingresos')->group(function () {
        Route::resource('ingresos', IngresoController::class);
    });

    // =====================================================
    // SALIDAS
    // =====================================================

    Route::middleware('modulo:salidas')->group(function () {
        Route::resource('salidas', SalidaController::class);
    });

    // =====================================================
    // CLIENTES
    // =====================================================

    Route::middleware('modulo:clientes')->group(function () {
        Route::resource('clientes', ClienteController::class)
            ->except(['show']);

        Route::post('/clientes/{cliente}/activar', [ClienteController::class, 'activar'])
            ->name('clientes.activar');
    });

    // =====================================================
    // VENTAS
    // =====================================================

    Route::middleware('modulo:ventas')->group(function () {
        Route::resource('ventas', VentaController::class);
    });

    // =====================================================
    // PAGOS
    // =====================================================

    Route::middleware('modulo:pagos')->group(function () {
        Route::get('/pagos', [PagoController::class, 'index'])
            ->name('pagos.index');

        Route::get('/pagos/create', [PagoController::class, 'create'])
            ->name('pagos.create');

        Route::post('/pagos', [PagoController::class, 'store'])
            ->name('pagos.store');

        Route::get('/pagos/{venta}', [PagoController::class, 'show'])
            ->name('pagos.show');
    });

    // =====================================================
    // INFORMES
    // =====================================================

    Route::middleware('modulo:informes')->group(function () {
        Route::get('/informes', [InformesController::class, 'index'])
            ->name('informes.index');
    });

});

require __DIR__.'/auth.php';
