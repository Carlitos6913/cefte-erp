# Diagramas de clases — CEFTE (metodología XP)

Fuente: clases reales del sistema (`app/Models`, `app/Services`, `app/Http/Controllers`, `app/Http/Middleware`).

Los diagramas siguen el avance por iteraciones de la metodología XP descrito en el Capítulo III:

| Iteración | Diagrama | Figuras del capítulo |
|---|---|---|
| 1 | Usuarios | Figura 2 |
| 2 | Inventario | Figura 11 |
| 3 | Comercial | Figura 21 |
| 4 | Reportes | Figura 29 |

> **Nota de nomenclatura.** Las clases físicas del código están en inglés por herencia de Laravel (por ejemplo `User`, `ProductVariant`). En los diagramas se presentan con su **nombre equivalente en castellano** (`Usuario`, `Variante`). La sección [Equivalencias](#equivalencias-clase--código) permite rastrear cada clase hasta su archivo real.

## Convenciones UML

- `+` miembro público · `-` miembro privado/protegido.
- En atributos: `id` = PK, `_id` = FK.
- En relaciones: `1`, `0..1`, `1..N`, `0..N`, `N..N` indican la multiplicidad.
- La flecha punteada (`..>`) es una dependencia/realización: el servicio o controlador utiliza a la clase destino.

---

## Iteración 1 — Diagrama de clases "Usuarios"

Administración del personal, las cuentas de acceso y la autorización por rol y módulos.

![Iteración 1](diagramas/clases_iter1_usuarios.png)

```mermaid
classDiagram
    direction TB

    class Empleado {
        +int id
        +string nombre
        +string apellido_paterno
        +string apellido_materno
        +string carnet_identidad
        +string telefono
        +string direccion
        +string profesion
        +string correo
        +bool estado
        +getNombreCompleto() string
        +usuario() HasOne
    }

    class Usuario {
        +int id
        +int empleado_id
        +string nombre
        +string correo
        +datetime correo_verificado_en
        +string contrasena
        +bool estado
        +bool debe_cambiar_contrasena
        +string token_recordar
        +empleado() BelongsTo
    }

    class Rol {
        +int id
        +string nombre
        +string guard
        +array modulos
    }

    class Permiso {
        +int id
        +string nombre
        +string guard
    }

    class UserService {
        +store(datos) Usuario
        +update(usuario, datos) Usuario
        +destroy(usuario) void
    }

    class ModuloMiddleware {
        +handle(peticion, siguiente) Response
    }

    class RoleMiddleware {
        +handle(peticion, siguiente, rol) Response
    }

    class MustChangePassword {
        +handle(peticion, siguiente) Response
    }

    Empleado "1" --> "0..1" Usuario : tiene cuenta
    Usuario "N" --> "M" Rol : posee
    Rol "N" --> "M" Permiso : concede
    UserService ..> Usuario : crea / actualiza
    UserService ..> Empleado : vincula
    UserService ..> Rol : asigna
    UserService ..> Permiso : concede
    ModuloMiddleware ..> Rol : verifica modulos
    RoleMiddleware ..> Rol : verifica rol
    MustChangePassword ..> Usuario : exige cambio
```

**Historias cubiertas:** HU-01 (acceso al sistema), HU-02 (gestión de empleados y usuarios).

---

## Iteración 2 — Diagrama de clases "Inventario"

Catálogo de productos y variantes, unidades de medida y registro de ingresos con su efecto en el kardex.

![Iteración 2](diagramas/clases_iter2_inventario.png)

```mermaid
classDiagram
    direction TB

    class Categoria {
        +int id
        +string codigo
        +string nombre
        +string descripcion
        +productos() HasMany
    }

    class Producto {
        +int id
        +int categoria_id
        +string codigo
        +string nombre
        +string descripcion
        +bool estado
        +categoria() BelongsTo
        +variantes() HasMany
    }

    class Variante {
        +int id
        +int producto_id
        +int unidad_medida_id
        +int unidad_base_id
        +int unidad_empaque_id
        +decimal factor_empaque
        +string sku
        +string sabor
        +string tipo_empaque
        +decimal contenido_neto
        +decimal stock \"(derivado: suma de lotes)\"
        +decimal stock_minimo
        +decimal precio_unitario
        +bool estado
        +unidadesCapturables() array
        +stockEnEmpaque() float
        +stock() int
        +producto() BelongsTo
        +unidadBase() BelongsTo
        +unidadEmpaque() BelongsTo
        +lotes() HasMany
    }

    class Lote {
        +int id
        +int producto_variante_id
        +string codigo
        +date fecha_caducidad
        +decimal cantidad_inicial
        +decimal stock
        +bool estado
        +variante() BelongsTo
        +movimientos() HasMany
        +scopeDisponibleFefo() Builder
    }

    class UnidadMedida {
        +int id
        +string nombre
        +string simbolo
        +variantes() HasMany
    }

    class Ingreso {
        +int id
        +string numero
        +date fecha
        +int encargado_id
        +int usuario_id
        +decimal total
        +string estado
        +string observaciones
        +detalles() HasMany
        +encargado() BelongsTo
        +usuario() BelongsTo
    }

    class IngresoDetalle {
        +int id
        +int ingreso_id
        +int variante_id
        +int lote_id
        +int unidad_id
        +int cantidad
        +decimal cantidad_original
        +decimal precio_unitario
        +decimal subtotal
        +ingreso() BelongsTo
        +variante() BelongsTo
        +lote() BelongsTo
    }

    class MovimientoInventario {
        +int id
        +int variante_id
        +int lote_id
        +int unidad_id
        +decimal entrada
        +decimal salida
        +decimal stock_anterior
        +decimal stock_nuevo
        +string referencia
        +int referencia_id
        +string numero
        +int usuario_id
        +variante() BelongsTo
        +lote() BelongsTo
    }

    class InventarioService {
        +registrarEntrada(varianteId, cantidad, loteId, referencia, referenciaId, numero, usuarioId, unidadId) void
        +obtenerOCrearLote(varianteId, codigo, fechaCaducidad) Lote
        +consumirSalidaFefo(varianteId, cantidad, referencia, referenciaId, numero, usuarioId, unidadId) array
        +revertirEntrada(varianteId, cantidad, loteId, referencia, referenciaId, usuarioId) void
        +revertirSalida(varianteId, cantidad, loteId, referencia, referenciaId, usuarioId) void
        +stockDisponible(varianteId) float
    }

    class CodigoService {
        +siguienteProducto() string
        +previsualizarProducto() string
        +siguienteSku(producto) string
        +previsualizarSku(producto) string
    }

    class NumeradorService {
        +siguiente(codigo, anio) string
    }

    Categoria "1" --> "0..N" Producto : agrupa
    Producto "1" --> "1..N" Variante : genera
    UnidadMedida "1" --> "0..N" Variante : mide
    Ingreso "1" --> "1..N" IngresoDetalle : contiene
    IngresoDetalle "N" --> "1" Variante : compra
    IngresoDetalle "N" --> "0..1" Lote : registra
    Variante "1" --> "0..N" Lote : se controla por
    Variante "1" --> "0..N" MovimientoInventario : registra
    Lote "1" --> "0..N" MovimientoInventario : afecta
    InventarioService ..> Variante : controla stock
    InventarioService ..> Lote : consume FEFO
    InventarioService ..> MovimientoInventario : crea asiento
    CodigoService ..> Producto : genera codigo
    CodigoService ..> Variante : genera sku
    NumeradorService ..> Ingreso : numera
```

**Historias cubiertas:** HU-03 (categorías), HU-04 (productos y variantes), HU-05 (ingresos de mercadería).

---

## Iteración 3 — Diagrama de clases "Comercial"

Salidas de almacén, ventas al contado y a crédito, y cobros con control del saldo pendiente.

![Iteración 3](diagramas/clases_iter3_comercial.png)

```mermaid
classDiagram
    direction TB

    class Cliente {
        +int id
        +string nombre
        +string ci_nit
        +string telefono
        +string correo
        +string direccion
        +bool estado
        +ventas() HasMany
    }

    class Venta {
        +int id
        +string numero
        +date fecha
        +int cliente_id
        +string cliente
        +int usuario_id
        +string pago_tipo
        +decimal saldo_pendiente
        +decimal total
        +string estado
        +string observaciones
        +detalles() HasMany
        +pagos() HasMany
        +clienteRelacion() BelongsTo
        +usuario() BelongsTo
    }

    class VentaDetalle {
        +int id
        +int venta_id
        +int variante_id
        +int unidad_id
        +int cantidad
        +decimal cantidad_original
        +decimal precio_unitario
        +decimal subtotal
        +venta() BelongsTo
        +variante() BelongsTo
        +salidaDetalles() BelongsToMany
    }

    class Pago {
        +int id
        +int venta_id
        +date fecha
        +decimal monto
        +string metodo
        +string observaciones
        +int usuario_id
        +venta() BelongsTo
        +usuario() BelongsTo
    }

    class Salida {
        +int id
        +string numero
        +date fecha
        +string motivo
        +int usuario_id
        +decimal total
        +string estado
        +string observaciones
        +detalles() HasMany
        +usuario() BelongsTo
        +autorizaVenta() bool
    }

    class SalidaDetalle {
        +int id
        +int salida_id
        +int variante_id
        +int unidad_id
        +int cantidad
        +int cantidad_vendida
        +decimal cantidad_original
        +decimal precio_unitario
        +decimal subtotal
        +salida() BelongsTo
        +variante() BelongsTo
        +ventaDetalles() BelongsToMany
        +saldoDisponible() int
    }

    class SalidaVentaService {
        +saldoDisponible(varianteId) int
        +saldosPorVariante() array
        +consumir(varianteId, cantidad, ventaDetalleId) void
        +liberar(ventaDetalleId) void
        +liberarVenta(ventaId) void
        +salidaTieneVendidos(salidaId) bool
    }

    Cliente "1" --> "0..N" Venta : adquiere
    Venta "1" --> "1..N" VentaDetalle : contiene
    Venta "1" --> "0..N" Pago : recibe
    Salida "1" --> "1..N" SalidaDetalle : contiene
    SalidaDetalle "N" --> "N" VentaDetalle : vincula
    SalidaVentaService ..> SalidaDetalle : consume
    SalidaVentaService ..> VentaDetalle : relaciona
```

**Historias cubiertas:** HU-06 (salidas), HU-07 (ventas), HU-08 (pagos).

---

## Iteración 4 — Diagrama de clases "Reportes"

Panel de control con indicadores e informes de ingresos y existencias.

![Iteración 4](diagramas/clases_iter4_reportes.png)

```mermaid
classDiagram
    direction TB

    class DashboardController {
        +__invoke() Response
        -calcularVentasHoy() decimal
        -calcularCuentasPorCobrar() decimal
        -contarProductosActivos() int
        -contarStockBajo() int
        -listarRecientes() array
    }

    class InformesController {
        +index(peticion) Response
        -resumenIngresos(desde, hasta) object
        -contarStockBajo() int
        -listarMovimientos(desde, hasta) LengthAwarePaginator
    }

    class Venta {
        +int id
        +date fecha
        +decimal total
        +decimal saldo_pendiente
        +string pago_tipo
        +string estado
    }

    class Ingreso {
        +int id
        +string numero
        +date fecha
        +decimal total
        +string estado
    }

    class Salida {
        +int id
        +string numero
        +date fecha
        +string motivo
        +string estado
    }

    class Variante {
        +int id
        +string sku
        +decimal stock \"(derivado: suma de lotes)\"
        +decimal stock_minimo
        +bool estado
    }

    class Lote {
        +int id
        +string codigo
        +date fecha_caducidad
        +decimal stock
        +bool estado
    }

    class MovimientoInventario {
        +int id
        +int lote_id
        +decimal entrada
        +decimal salida
        +decimal stock_anterior
        +decimal stock_nuevo
        +string referencia
    }

    DashboardController ..> Venta : ventasHoy / cuentasPorCobrar
    DashboardController ..> Variante : totalProductos / stockBajo
    DashboardController ..> Ingreso : ingresosRecientes
    DashboardController ..> Salida : salidasRecientes
    InformesController ..> Ingreso : resumen y movimientos
    InformesController ..> Variante : stock bajo minimo
    InformesController ..> Lote : existencias por lote
    InformesController ..> MovimientoInventario : kardex
```

**Historia cubierta:** HU-09 (panel de control e informes).

---

## Equivalencias clase ↔ código

| Clase en el diagrama | Clase / archivo real | Iteración |
|---|---|---|
| Empleado | `App\Models\Employee` | 1 |
| Usuario | `App\Models\User` | 1 |
| Rol | `Spatie\Permission\Models\Role` | 1 |
| Permiso | `Spatie\Permission\Models\Permission` | 1 |
| UserService | `App\Services\UserService` | 1 |
| ModuloMiddleware | `App\Http\Middleware\ModuloMiddleware` | 1 |
| RoleMiddleware | `App\Http\Middleware\RoleMiddleware` | 1 |
| MustChangePassword | `App\Http\Middleware\MustChangePassword` | 1 |
| Categoría | `App\Models\Category` | 2 |
| Producto | `App\Models\Product` | 2 |
| Variante | `App\Models\ProductVariant` | 2 |
| UnidadMedida | `App\Models\UnitMeasure` | 2 |
| Ingreso | `App\Models\Ingreso` | 2 |
| IngresoDetalle | `App\Models\IngresoDetalle` | 2 |
| MovimientoInventario | `App\Models\MovimientoInventario` | 2 |
| InventarioService | `App\Services\InventarioService` | 2 |
| CodigoService | `App\Services\CodigoService` | 2 |
| NumeradorService | `App\Services\NumeradorService` | 2 |
| Cliente | `App\Models\Cliente` | 3 |
| Venta | `App\Models\Venta` | 3 |
| VentaDetalle | `App\Models\VentaDetalle` | 3 |
| Pago | `App\Models\Pago` | 3 |
| Salida | `App\Models\Salida` | 3 |
| SalidaDetalle | `App\Models\SalidaDetalle` | 3 |
| SalidaVentaService | `App\Services\SalidaVentaService` | 3 |
| DashboardController | `App\Http\Controllers\Admin\DashboardController` | 4 |
| InformesController | `App\Http\Controllers\Admin\InformesController` | 4 |

### Atributos cuyo nombre físico está en inglés

| Clase | Atributo en el diagrama | Columna real |
|---|---|---|
| Empleado | nombre | first_name |
| Empleado | apellido_paterno | first_surname |
| Empleado | apellido_materno | second_surname |
| Empleado | carnet_identidad | identity_card |
| Empleado | telefono | phone |
| Empleado | direccion | address |
| Empleado | profesion | profession |
| Empleado | correo | email |
| Empleado | estado | is_active |
| Usuario | nombre | name |
| Usuario | correo | email |
| Usuario | contrasena | password |
| Usuario | estado | is_active |
| Usuario | debe_cambiar_contrasena | must_change_password |
| Usuario | token_recordar | remember_token |
| Rol/Permiso | nombre, guard | name, guard_name |
| Categoría/Producto | codigo, nombre, descripcion | code, name, description |
| Variante | sabor, tipo_empaque, contenido_neto, stock_minimo, precio_unitario | flavor, packaging_type, net_content, minimum_stock, unit_price |
| (varias) | variante_id | product_variant_id |
| (varias) | usuario_id | user_id |

---

## Regenerar las figuras

```powershell
npx -y @mermaid-js/mermaid-cli -i docs\diagramas\clases_iter1_usuarios.mmd -o docs\diagramas\clases_iter1_usuarios.png -b white -s 2
npx -y @mermaid-js/mermaid-cli -i docs\diagramas\clases_iter1_usuarios.mmd -o docs\diagramas\clases_iter1_usuarios.svg -b white
```

Si mermaid-cli no encuentra Chrome, indícale la ruta con `-p config.json`:

```json
{ "executablePath": "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe", "args": ["--no-sandbox"] }
```
