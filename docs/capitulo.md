# Sistema de Gestión de Inventario para Procesamiento de Alimentos

> Documento técnico del capítulo correspondiente al sistema **CEFTE**, centro de procesamiento de alimentos.
> Versión vigente: **30/09/2026**. Reflecte el estado real del código en `D:\Laragon\laragon\www\cefte-erp`.

---

## Índice

1. [Contexto y alcance](#1-contexto-y-alcance)
2. [Stack tecnológico](#2-stack-tecnológico)
3. [Arquitectura](#3-arquitectura)
4. [Modelo de datos](#4-modelo-de-datos)
5. [Servicios de dominio](#5-servicios-de-dominio)
6. [Módulos funcionales](#6-módulos-funcionales)
7. [Seguridad y control de acceso](#7-seguridad-y-control-de-acceso)
8. [Inicialización de datos](#8-inicialización-de-datos)
9. [Estrategia de pruebas](#9-estrategia-de-pruebas)
10. [Operación y mantenimiento](#10-operación-y-mantenimiento)
11. [Trazabilidad](#11-trazabilidad)
12. [Deuda técnica conocida](#12-deuda-técnica-conocida)

---

## 1. Contexto y alcance

El sistema administra el inventario, el personal, la comercialización y la cobranza de una planta de procesamiento de alimentos. Cubre el ciclo completo desde la recepción de mercadería hasta el cobro de la venta:

- Recepción de mercadería (ingresos) con responsable asignado del padrón de empleados.
- Despacho y mermas (salidas) con descuento de existencias.
- Venta al contado y a crédito, con autorización de despacho obligatoria.
- Registro de pagos parciales y totales sobre ventas.
- Consulta de indicadores: stock mínimo, venta del día, saldos pendientes.
- Control de acceso por rol y por módulo.

### 1.1 Fuera de alcance

El sistema no incluye compras a proveedores, cuentas por pagar, facturación electrónica, ni control de producción (recetas, lotes o MPR). El control de costos es promedio simple, sin costeo por valoración.

---

## 2. Stack tecnológico

### 2.1 Backend

| Componente | Tecnología | Versión |
|---|---|---|
| Lenguaje | PHP | `^8.3` |
| Framework | Laravel | `^13.8` |
| ORM | Eloquent | (incluido en Laravel) |
| Autenticación | Sesión nativa | — |
| RBAC | Spatie Laravel Permission | `^8.3` |
| Puertas de enlace | Ziggy | `^2.0` |
| API | Laravel Sanctum | `^4.0` |
| Consola REPL | Laravel Tinker | `^3.0` |

### 2.2 Frontend

| Componente | Tecnología | Versión |
|---|---|---|
| Framework SPA | Inertia.js | `^2.0` |
| UI | Vue 3 (Composition API) | `^3.4` |
| Estilos | Tailwind CSS | `^3.2.1` |
| Build | Vite | `^8.0` |
| Utilidades | lodash | `^4.18.1` |

### 2.3 Calidad y base de datos

| Componente | Tecnología | Versión |
|---|---|---|
| Pruebas | PHPUnit | `^12.5.12` |
| Formato de código | Laravel Pint | `^1.27` |
| Base de datos (producción) | MySQL 8 | — |
| Base de datos (pruebas) | SQLite en memoria | `:memory:` |

---

## 3. Arquitectura

### 3.1 Estilo

Aplicación monolítica con separación por capas dentro de un mismo despliegue:

```
resources/js/  ──HTTP──▶  app/Http/Controllers/Admin/
                                    │
                                    ├──▶ app/Models/          (lectura y persistencia)
                                    └──▶ app/Services/        (reglas de negocio)
                                               │
                                               └──▶ app/Models/  (escritura transaccional)

database/migrations/  ──▶  esquema
database/seeders/     ──▶  datos de demostración
tests/                ──▶  verificación de las capas anteriores
```

La comunicación con el cliente es **server-driven**: el servidor devuelve una página Inertia y el cliente se rehidrata sin recarga completa. No hay API JSON pública consumida por el frontend; Sanctum queda instalado pero sin `guards.api` configurado.

### 3.2 Capas y su responsabilidad

| Capa | Ubicación | Responsabilidad |
|---|---|---|
| Presentación | `resources/js/Pages`, `Components` | Formularios, tablas y navegación. Sin reglas de negocio. |
| Transporte | `app/Http/Controllers` | Validación de entrada, orquestación y respuesta Inertia. |
| Dominio | `app/Services` | Reglas críticas: stock, Kardex, correlativos, códigos y alta de usuarios. |
| Persistencia | `app/Models` | Mapeo objeto-relacional, relaciones y conversions de tipo. |
| Infraestructura | `database/migrations`, `seeders` | Esquema y datos iniciales. |

### 3.3 Servicios

Cinco servicios, resueltos por inyección de dependencias de tipo:

| Servicio | Responsabilidad |
|---|---|
| `InventarioService` | Movimiento de existencias y asiento en Kardex. |
| `SalidaVentaService` | Saldo autorizado para venta y su consumo FIFO. |
| `NumeradorService` | Correlativos documentales por año. |
| `CodigoService` | Códigos de catálogo y SKU consecutivos. |
| `UserService` | Alta, edición y baja de usuarios con su ficha de empleado. |

`InventarioService`, `SalidaVentaService` y `NumeradorService` **no abren transacción propia**: se ejecutan dentro de la transacción abierta por el llamador. Esto garantiza que el documento y sus asientos de inventario se confirmen o reviertan en bloque.

### 3.4 Componentes de interfaz

- **Layouts**: `AppLayout` (aplicación), `GuestLayout` (visitantes), `AuthenticatedLayout`.
- **Layout**: `Navbar`, `Sidebar`, `Footer`.
- **UI reutilizable**: `Button`, `Card`, `Input`, `Table`, `Badge`, `Pagination`, `SearchBox`.
- **Base Breeze**: `Modal`, `Dropdown`, `NavLink`, `Checkbox` y demás.
- **Páginas**: 52 archivos Vue organizados por módulo.

En total el frontend reúne **78 componentes Vue**: 52 páginas, 3 layouts, 23 componentes de interfaz y los componentes base heredados de Breeze.

El `Sidebar.vue` reconstruye el menú a partir del primer rol del usuario y de su columna `roles.modulos`. Si `modulos` es `null`, el menú se muestra completo.

---

## 4. Modelo de datos

### 4.1 Diagrama entidad-relación

```
employees ──1:1── users ──N:M── roles
   │                │
   │                ├──1:N── ingresos ──1:N── ingreso_detalles
   └──1:N── ingresos
                    ├──1:N── salidas ──1:N── salida_detalles
                    ├──1:N── ventas  ──1:N── venta_detalles
                    ├──1:N── pagos
                    └──1:N── movimientos_inventario

categories ──1:N── products ──1:N── product_variants ──┬──1:N── ingreso_detalles
                          ▲                            ├──1:N── salida_detalles
                          │                            ├──1:N── venta_detalles
unit_measures ────────────┘                            └──1:N── movimientos_inventario

clientes ──1:N── ventas ──1:N── venta_detalles ──N:M── salida_detalles
                    │                                   (pivote con cantidad)
                    └──1:N── pagos
```

### 4.2 Catálogo de productos

El catálogo se separa en dos niveles porque una misma presentación puede tener varios formatos:

- `products` — el bien genérico (por ejemplo, *Leche entera UHT*).
- `product_variants` — la presentación comercializable con SKU propio (por ejemplo, *Leche entera UHT 1 L*).

Ambos depende de `categories` y `unit_measures`. La existencia vive en `product_variants.stock`, nunca en `products`.

El SKU se compone como `{codigo_producto}-{ordinal}`, por ejemplo `PROD-000001-1`, donde el ordinal es el número de variantes del producto.

### 4.3 Documentos de inventario

`ingresos`, `salidas` y `ventas` comparten la misma forma: cabecera con número, fecha, total y estado, más una tabla de detalles.

| Tabla | Contenido relevante |
|---|---|
| `ingresos` | `numero` (único), `fecha`, `encargado_id` **obligatorio**, `observaciones`, `total`, `estado`, `user_id` |
| `ingreso_detalles` | `product_variant_id`, `cantidad`, `precio_unitario`, `subtotal` |
| `salidas` | `numero`, `fecha`, `motivo`, `observaciones`, `total`, `estado`, `user_id` |
| `salida_detalles` | `product_variant_id`, `cantidad`, `cantidad_vendida`, `precio_unitario`, `subtotal` |
| `ventas` | `numero`, `fecha`, `cliente_id`, `pago_tipo`, `total`, `saldo_pendiente`, `estado`, `user_id` |
| `venta_detalles` | `product_variant_id`, `cantidad`, `precio_unitario`, `subtotal` |
| `pagos` | `venta_id`, `fecha`, `monto`, `metodo`, `user_id` |

### 4.4 Kardex

`movimientos_inventario` es el libro de existencias. Cada fila registra un asiento con:

- `product_variant_id` y `user_id`.
- `entrada` y `salida` (una de las dos es cero).
- `stock_anterior` y `stock_nuevo`, de modo que el saldo es verificable leyendo el historial.
- `referencia` (`ingreso` o `salida`) y `referencia_id`, que enlaza el asiento con su documento.
- `numero` del documento origen.

La tabla no usa clave foránea hacia `ingresos` ni `salidas`: el vínculo es lógico (`referencia` + `referencia_id`), lo que permite reutilizar la misma estructura para ambos documentos.

### 4.5 Saldo autorizado para venta

Modelo de tres piezas:

1. `salida_detalles.cantidad_vendida` — cuánto de esa línea ya se vendió.
2. `salida_detalle_venta_detalle` — tabla pivote que registra qué línea de venta consumió qué línea de salida, con su `cantidad`.
3. `SalidaDetalle::saldo_disponible` — accessor que calcula `cantidad - cantidad_vendida`.

La regla es: **una venta solo puede consumir existencias previamente despachadas en una salida con `motivo = 'venta'` y `estado = 'activo'`**. Las salidas por merma, daño o consumo interno no habilitan venta.

### 4.6 Correlativos y secuencias

Dos mecanismos distintos, porque responden a necesidades distintas:

| Tabla | Servicio | Comportamiento |
|---|---|---|
| `correlativos` | `NumeradorService` | Numeración documental **por año**: `ING-2026-000001`. Se reinicia cada año. |
| `secuencias` | `CodigoService` | Códigos de catálogo **históricos**: `PROD-000001`. Nunca se reinician. |

`NumeradorService::siguiente('ING')` devuelve `ING-2026-000001`. `CodigoService::siguienteProducto()` devuelve `PROD-000001`.

Ambos bloquean la fila con `lockForUpdate()` e incrementan de forma atómica, por lo que dos peticiones concurrentes no obtienen el mismo número.

### 4.7 Personal y usuarios

`employees` y `users` están separados a propósito:

- `employees` — datos personales y laborales: nombres, C.I., teléfono, dirección, profesión, correo, estado.
- `users` — credenciales: correo, contraseña, rol, y `employee_id` opcional.

Una ficha de empleado puede existir sin usuario (personal sin acceso al sistema) y un usuario puede no tener ficha asociada. La relación es `1:1` opcional desde el lado de `users`.

El acceso se modela con **dos atributos**: `users.employee_id` (a quién pertenece la cuenta) e `ingresos.encargado_id` (quién recibió la mercadería). Son responsabilidades distintas y se documentan por separado.

### 4.8 Roles

La tabla `roles` de Spatie se extiende con una columna `modulos` (JSON nullable) añadida por migración propia.

| Rol | `modulos` | Alcance |
|---|---|---|
| `Administrador` | `null` | Acceso total |
| `Gerente` | `null` | Acceso total |
| `Almacenero` | `categorias, unidades, productos, variantes, ingresos, salidas, informes` | Inventario |
| `Vendedor` | `productos, variantes, clientes, ventas, pagos` | Comercial |

`null` significa acceso total. Una lista vacía `[]` significa algo distinto: el usuario no tiene ningún módulo, y el `Sidebar.vue` le oculta todo el menú. Esta distinción está fijada por pruebas de regresión (`tests/Feature/ModulosPorRolTest.php`).

No se crean permisos granulares: el control de acceso real es **rol + módulo**, no permiso individual.

### 4.9 Cronología de migraciones

| Fecha | Migraciones | Módulo |
|---|---|---|
| 04/08/2026 | Tablas de permisos de Spatie, `deleted_at` en usuarios | Seguridad |
| 08/08/2026 – 10/08/2026 | Productos, ingresos, ingreso_detalles, estado del ingreso | Inventario |
| 13/08/2026 | Categorías y `categoria_id` en productos | Inventario |
| 19/08/2026 | Ventas, venta_detalles, clientes, forma de pago, pagos | Comercial |
| 25/08/2026 – 26/08/2026 | Estado del usuario, módulos por rol, salidas y salida_detalles | Seguridad e inventario |
| 09/09/2026 – 10/09/2026 | Dirección del usuario, empleados, normalización de `users` | Personal |
| 24/09/2026 | Catálogo normalizado (`categories`, `unit_measures`, `products`, `product_variants`) y migración de datos | Inventario |
| 25/09/2026 | Cambio obligatorio de contraseña, correlativos, Kardex, `cantidad_vendida`, tabla pivote | Integración |
| 30/09/2026 | `secuencias`, `encargado_id` obligatorio en ingresos con eliminación de `proveedor` | Inventario |

Total: **34 migraciones**, todas reversibles.

---

## 5. Servicios de dominio

### 5.1 `InventarioService`

Es el único punto autorizado para modificar existencias.

```php
registrarMovimiento(
    int $productVariantId, string $movimiento, float $cantidad,
    string $referencia, int $referenciaId, ?string $numero, ?int $userId = null
): MovimientoInventario
```

Secuencia interna:

1. `ProductVariant::lockForUpdate()` — bloquea la variante contra escrituras concurrentes.
2. Calcula `stock_nuevo` sumando o restando según `$movimiento` sea `entrada` o `salida`.
3. Si `stock_nuevo` fuera negativo, lanza `RuntimeException` con el mensaje `Stock insuficiente para la variante {sku}. Disponible: {n}.`
4. Actualiza `stock` y inserta el asiento con `stock_anterior` y `stock_nuevo`.

`revertir()` invierte un movimiento previo: un asiento de `ingreso` genera una `salida` compensatoria y viceversa. Se usa al anular documentos.

### 5.2 `SalidaVentaService`

| Método | Comportamiento |
|---|---|
| `saldoDisponible(int $varianteId): int` | Suma `cantidad - cantidad_vendida` de salidas `venta` activas. |
| `saldosPorVariante(): array` | Igual, agrupado por variante, solo saldos mayores a cero. |
| `consumir(int $varianteId, int $cantidad, int $ventaDetalleId): void` | Consume FIFO por fecha de salida; escribe en el pivote. |
| `liberar(int $ventaDetalleId): void` | Devuelve al lote lo consumido por una línea de venta. |
| `liberarVenta(int $ventaId): void` | Libera todas las líneas de una venta. |
| `salidaTieneVendidos(int $salidaId): bool` | Indica si una salida ya tiene mercadería vendida. |

El consumo recorre las salidas ordenadas por `fecha` e `id`, con `lockForUpdate()`, e incrementa `cantidad_vendida`. Si el saldo insuficiente, lanza `RuntimeException` y la venta se rechaza completa, porque la transacción del llamador revierte el ingreso de la venta.

`saldosPorVariante()` incluye `ORDER BY sd.product_variant_id`. Sin ese orden, MySQL no garantiza un orden estable y los formularios de venta presentingían las variantes en un orden distinto en cada consulta.

### 5.3 `NumeradorService`

```php
siguiente(string $codigo, ?int $anio = null): string
```

Bloquea la fila de `correlativos` para el par `(codigo, anio)`, la crea con `ultimo = 0` si no existe, incrementa y devuelve `PREFIX-AAAA-NNNNNN`. Códigos en uso: `ING`, `SAL`, `VEN`.

### 5.4 `CodigoService`

| Método | Resultado |
|---|---|
| `siguienteProducto()` | `PROD-000001` |
| `siguienteSku(Product $p)` | `PROD-000001-1` |
| `previsualizarProducto()` | `PROD-000007` (sin consumir) |
| `previsualizarSku(Product $p)` | `PROD-000007-1` (sin consumir) |

Los métodos `previsualizar*` leen el valor actual sin bloquear ni incrementar, para mostrar el código que le tocará al usuario en el formulario antes de confirmar.

### 5.5 `UserService`

Concentra el alta de usuarios porque el proceso tiene pasos que no deben quedar sueltos en el controlador:

- Si el rol es `__nuevo__`, crea el rol con su lista de módulos.
- Obtiene o crea la ficha de empleado asociada.
- Contraseña inicial: el **C.I. del empleado**.
- Correo por defecto: `{ci}@cefte.edu.bo`.
- Deja `must_change_password = true`.
- Asigna el rol con `assignRole()` o `syncRoles()`.

`destroy()` realiza baja lógica: `is_active = false`, sin borrar la fila.

---

## 6. Módulos funcionales

### 6.1 Inventario

| Módulo | Rutas | Descripción |
|---|---|---|
| Categorías | `categorias` | Familias de producto. |
| Unidades | `unidades` | Unidad, litro, kilogramo, caja, bolsa, etc. |
| Productos | `productos` | Bienes genéricos del catálogo. |
| Variantes | `variantes` | Presentaciones con SKU y existencia. |
| Ingresos | `ingresos` | Recepción de mercadería. |
| Salidas | `salidas` | Despacho, merma y consumo. |

Los ingresos exigen `encargado_id` válido. El formulario de alta y de edición ofrece **solo empleados activos**, ordenados por apellido y nombre, mostrando nombre completo y C.I.

La anulación de un ingreso o salida invierte el stock mediante `InventarioService::revertir()` y exige rol `Administrador` o `Gerente`. Una salida con `cantidad_vendida > 0` no puede anularse, porque la mercancía ya fue vendida.

### 6.2 Comercial

| Módulo | Rutas | Descripción |
|---|---|---|
| Clientes | `clientes` | Padrón de clientes con C.I./NIT. |
| Ventas | `ventas` | Venta al contado y a crédito. |
| Pagos | `pagos` | Cobros parciales y totales. |

La venta valida en cascada:

1. Que exista saldo autorizado suficiente para cada variante.
2. Que el saldo pendiente no exceda el total.
3. Que la suma de pagos no supere el saldo.

`SalidaDetalle` expone `saldo_disponible`, que el frontend usa para mostrar el máximo vendible por línea.

### 6.3 Panel e informes

`DashboardController` calcula venta del día, saldos pendientes de ventas a crédito, variantes activas y variantes en stock mínimo mediante `whereColumn('stock', '<=', 'minimum_stock')`.

`InformesController` filtra por rango de fechas y delega las agregaciones a SQL (`SUM`, `COUNT`) en lugar de traer registros a memoria. Paginación de 15 registros, con `eager loading` de relaciones anidadas. La columna del responsable del ingreso se muestra tanto a administradores como a gerentes.

### 6.4 Personal

- `empleados` — padrón de personal con alta, edición, baja lógica y activación.
- `usuarios` — cuentas de acceso, cada una vinculada opcionalmente a una ficha de empleado.
- La ruta `empleados/{empleado}/usuario` permite crear o reasignar la cuenta desde la propia ficha.

---

## 7. Seguridad y control de acceso

### 7.1 Capas de control

El acceso se verifica en **cuatro capas**, en este orden:

1. **Autenticación** — `auth`: exige sesión iniciada.
2. **Verificación de correo** — `verified`: solo en `dashboard`.
3. **Módulo** — `modulo:x`: comprueba que el rol tenga el módulo en su lista.
4. **Rol** — `role:X,Y`: reserva operaciones sensibles a los roles indicados.

Ejemplo real de una ruta protegida:

```
DELETE /ingresos/{ingreso}
  middleware: web, auth, modulo:ingresos, role:Administrador,Gerente
```

### 7.2 Módulos protegidos

`usuarios`, `empleados`, `categorias`, `unidades`, `productos`, `variantes`, `ingresos`, `salidas`, `clientes`, `ventas`, `pagos`, `informes`.

### 7.3 Restricciones por rol

| Operación | Roles autorizados |
|---|---|
| Eliminar usuario, activar usuario | `Administrador` |
| Eliminar empleado | `Administrador` |
| Eliminar categoría, unidad, producto, variante | `Administrador` |
| Eliminar ingreso, salida, venta | `Administrador`, `Gerente` |

`RoleMiddleware` usa `hasAnyRole()` y devuelve 403 si la condición no se cumple. `ModuloMiddleware` toma el **primer** rol del usuario; si no tiene rol, devuelve 403.

### 7.4 Credenciales

- Contraseñas con hash `bcrypt`.
- Verificación de correo habilitada en `dashboard`.
- Restablecimiento de contraseña con token de 60 minutos de expiración y 60 segundos de espera entre solicitudes.
- Confirmación de contraseña con ventana de 3 horas (`password_timeout = 10800`).

### 7.5 Cambio obligatorio de contraseña

`MustChangePassword` revisa `users.must_change_password`. Si está activo, el usuario solo puede navegar a `password.cambio`, enviar el nuevo valor o cerrar sesión; cualquier otra ruta redirige al formulario de cambio.

Todo usuario creado por `UserService` nace con esta bandera activa, de modo que ninguna credencial inicial sobrevive al primer ingreso.

### 7.6 Sesión

Driver `web` con cookies. Sanctum está instalado pero no se usa para autenticación de API en el estado actual.

---

## 8. Inicialización de datos

### 8.1 Orden de ejecución

`DatabaseSeeder` invoca los seeders en este orden, por dependencias:

```
1. RolesYPermisosSeeder
2. UnidadesMedidaSeeder
3. EmpleadosYUsuariosSeeder
4. CatalogoSeeder
5. IngresosSeeder
6. ClientesSeeder
7. SalidasVentasPagosSeeder
```

El orden importa: el catálogo debe existir antes que los ingresos, y los ingresos antes que las salidas y ventas, porque cada documento consume el saldo acumulado por el anterior.

### 8.2 Contenido

| Seeder | Filas generadas |
|---|---|
| `RolesYPermisosSeeder` | 4 roles |
| `UnidadesMedidaSeeder` | 8 unidades |
| `EmpleadosYUsuariosSeeder` | 8 empleados, 5 usuarios |
| `CatalogoSeeder` | 5 categorías, 20 productos, 24 variantes |
| `IngresosSeeder` | 30 ingresos, 60 detalles, 60 movimientos |
| `ClientesSeeder` | 8 clientes |
| `SalidasVentasPagosSeeder` | 12 salidas, 36 detalles, 12 ventas, 24 detalles, 4 pagos, 36 movimientos |

Resultado de una carga limpia: **96 asientos de Kardex** (60 de ingreso, 36 de salida), con los motivos de salida repartidos en 9 despachos y 3 mermas.

| Documento | Estados generados |
|---|---|
| `ingresos` | 30 activos |
| `salidas` | 12 activos (9 `venta`, 3 `merma`) |
| `ventas` | 12 activos |

### 8.3 Comportamiento de los datos generados

- Todos los movimientos de stock pasan por `InventarioService`, igual que la aplicación. El seed no escribe `stock` directamente.
- Las ventas se consumen contra salidas reales mediante `SalidaVentaService::consumir()`. No se simulan saldos.
- `IngresosSeeder` reparte el responsable entre los empleados activos, rotando por día.
- `SalidasVentasPagosSeeder` alterna motivos `venta` y `merma`, y genera ventas con saldo pendiente más pagos parciales del 50 %.

### 8.4 Catálogo de alimentos

| Código | Categoría | Productos | Variantes |
|---|---|---|---|
| `BEB` | Bebidas y Jugos | 5 | 7 |
| `CON` | Conservas y Enlatados | 4 | 4 |
| `LAC` | Lácteos y Quesos | 4 | 5 |
| `PAN` | Panificación y Repostería | 4 | 4 |
| `DES` | Deshidratados e Instanáneos | 3 | 4 |
| | **Total** | **20** | **24** |

El catálogo se declara como una constante en `CatalogoSeeder`: cada producto declara sus propias variantes, y la variante distingue por tamaño, envase o sabor. El código del producto y el SKU se generan por servicio, no a mano.

> En una base con uso real los totales pueden diferir: las categorías, productos y variantes creados desde la interfaz se suman a los del seed. Los conteos de esta tabla corresponden a una carga limpia.

### 8.5 Comando

```bash
php artisan migrate:fresh --seed
```

> Los seeders **no son idempotentes**: ejecutarlos de forma aislada duplica documentos. Para recargar datos use siempre `migrate:fresh --seed`.

### 8.6 Credenciales de demostración

| Usuario | Correo | Contraseña inicial |
|---|---|---|
| Administrador | `carlos.muiba@cefte.edu.bo` | C.I. del empleado (`1234567 LP`, con espacio) |

El correo sigue el patrón `{ci}@cefte.edu.bo`. Todas las cuentas nacen con `must_change_password = true`.

---

## 9. Estrategia de pruebas

### 9.1 Resultado actual

```
PHPUnit 12.5.33
Tests:    80
Passed:   80
Asserts:  285
Time:     ~7 s
```

### 9.2 Niveles

| Nivel | Alcance |
|---|---|
| Unitario | Funciones puras y caso base del framework. |
| Funcional (HTTP) | Comportamiento del sistema por sus rutas, con efectos persistidos. |
| Integración de reglas | Validación de las reglas de inventario y autorización de venta. |
| Esquema | Integridad de migraciones: columnas, nulabilidad y llaves foráneas. |

### 9.3 Cobertura por archivo

| Archivo | Pruebas | Área |
|---|---|---|
| `FlujoSalidaVentaTest` | 9 | Despacho, venta, FIFO, anulación |
| `CodigoProductoTest` | 8 | Códigos y SKU del catálogo |
| `AsignarUsuarioEmpleadoTest` | 6 | Vínculo usuario-empleado |
| `ProfileTest` | 5 | Perfil |
| `ModulosPorRolTest` | 5 | Acceso por módulo y `null` vs `[]` |
| `KardexTest` | 5 | Asientos y anulaciones |
| `IngresoEncargadoTest` | 5 | Responsable del ingreso |
| `AutorizacionRolTest` | 5 | Restricciones por rol |
| `CambioContrasenaTest` | 5 | Ciclo de vida de contraseña |
| `IngresoEncargadoMigrationTest` | 4 | Esquema de `encargado_id` |
| `EdicionVentaPagosTest` | 3 | Redistribución de saldo y pagos |
| `Auth/*` | 16 | Autenticación y recuperación |
| `CorrelativosTest` | 2 | Secuencia documental |
| `ExampleTest` | 2 | Base del framework |

### 9.4 Utilidades compartidas

El trait `tests/Concerns/CreaCatalogos.php` evita duplicación:

- `crearVariante(stock, precio)` — categoría, unidad, producto y variante de una vez.
- `crearEncargado(activo)` — empleado del padrón.
- `usuario(rol)` — usuario autenticado con rol asignado.

### 9.5 Casos de regresión que fijan contrato

Estos casos existen para impedir que una modificación futura revierta una decisión deliberada:

- **Acceso total vs. sin permisos.** `ModulosPorRolTest` verifica que los roles de acceso total guarden `modulos = null` y nunca `[]`, porque el servidor interpreta ambos valores de forma distinta al sidebar.
- **Responsable obligatorio.** `IngresoEncargadoTest` y `IngresoEncargadoMigrationTest` fijan que `encargado_id` es obligatorio en la validación, en el modelo y en el esquema.
- **Venta sin despacho.** `FlujoSalidaVentaTest` verifica que una salida por merma no habilita la venta.
- **Código no consumido por previsualización.** `CodigoProductoTest` verifica que `previsualizar*` no incrementa la secuencia.

### 9.6 Convenciones

Las pruebas usan nombres en español con verbo de comportamiento: `test_registrar_ingreso_exige_un_encargado_valido`. Cada prueba es autónoma y arranca con `RefreshDatabase`.

### 9.7 Comandos de verificación

```bash
php artisan test              # Suite completa
vendor/bin/pint --test        # Estilo de código
npm run build                 # Compilación del frontend
```

---

## 10. Operación y mantenimiento

### 10.1 Puesta en marcha

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run dev
```

En Windows, `scripts/servir-en-red.ps1` levanta el entorno accesible desde otros equipos de la red local.

### 10.2 Comandos de artisan

| Comando | Función |
|---|---|
| `migrate:fresh --seed` | Recrear esquema y datos de demostración |
| `migrate` | Aplicar migraciones pendientes |
| `migrate:rollback` | Revertir la última tanda |
| `test` | Ejecutar la suite |
| `route:list` | Listar rutas y middleware |
| `tinker` | Consola interactiva |

### 10.3 Verificación de integridad

Las invariantes que conviene comprobar tras cargar datos o migrar:

```sql
-- El stock debe coincidir con la suma del Kardex
SELECT pv.id, pv.stock,
       COALESCE(SUM(mi.entrada), 0) - COALESCE(SUM(mi.salida), 0) AS kardex
FROM product_variants pv
LEFT JOIN movimientos_inventario mi ON mi.product_variant_id = pv.id
GROUP BY pv.id, pv.stock
HAVING kardex <> pv.stock;
```

Debe devolver **cero filas**. Si devuelve alguna, hay stock escrito fuera de `InventarioService` (por ejemplo, una carga manual o un formulario de alta de variante con existencia inicial).

### 10.4 Copias de seguridad

El catálogo de productos y sus existencias son el activo más sensible. Cualquier procedimiento de restauración debe verificarse con la consulta anterior antes de declarar la base como sana.

---

## 11. Trazabilidad

### 11.1 Requisitos y mecanismo

| Requisito | Mecanismo | Verificación |
|---|---|---|
| Trazabilidad de existencias | Kardex con `stock_anterior` y `stock_nuevo` | `KardexTest` |
| Nadie despacha lo que no existe | `InventarioService` con `lockForUpdate()` | `FlujoSalidaVentaTest` |
| No se vende sin despacho | Saldo autorizado FIFO | `FlujoSalidaVentaTest` |
| La venta no descuenta stock dos veces | Separación entre stock físico y saldo autorizado | `FlujoSalidaVentaTest` |
| Número de documento único y consecutivo | `NumeradorService` con bloqueo de fila | `CorrelativosTest` |
| Código de catálogo sin reinicios | `CodigoService` sobre `secuencias` | `CodigoProductoTest` |
| Cada ingreso tiene responsable | `encargado_id` obligatorio en tres capas | `IngresoEncargadoTest`, `IngresoEncargadoMigrationTest` |
| Acceso mínimo privilegio | `ModuloMiddleware` + `RoleMiddleware` | `ModulosPorRolTest`, `AutorizacionRolTest` |
| Credenciales iniciales no sobreviven | `must_change_password` | `CambioContrasenaTest` |
| Integridad de la base | Restricciones `NOT NULL` y llaves foráneas | `IngresoEncargadoMigrationTest` |

### 11.2 Trazabilidad de la recepción

La decisión de reemplazar `ingresos.proveedor` (texto libre) por `ingresos.encargado_id` (llave foránea) se aplicó en tres capas, cada una con su prueba:

| Capa | Mecanismo | Prueba |
|---|---|---|
| Esquema | `NOT NULL` + FK a `employees` | `test_encargado_es_obligatorio_y_referencia_a_empleados` |
| Aplicación | `required\|exists:employees,id` | `test_registrar_ingreso_exige_un_encargado_valido` |
| Interfaz | Desplegable de empleados activos | `test_el_formulario_solo_ofrece_empleados_activos` |

La eliminación del texto libre elimina la posibilidad de responsables con grafías divergentes ("Quispe", "quispe", "Quispe H.") o inexistentes en el padrón.

---

## 12. Deuda técnica conocida

Puntos identificados que no afectan la operación pero conviene resolver:

| # | Hallazgo | Impacto | Acción sugerida |
|---|---|---|---|
| 1 | `database/seeders/RolesAndPermissionsSeeder.php` existe pero no lo invoca `DatabaseSeeder` | Confusión; crea roles sin `modulos` ni `guard_name` | Eliminar el archivo |
| 2 | `2026_08_04_150259_add_deleted_at_to_users_table.php` añade `deleted_at`, pero ningún modelo usa `SoftDeletes` | Columna muerta | Eliminar columna o activar el trait |
| 3 | `@tailwindcss/vite ^4.0.0` junto a `tailwindcss ^3.2.1` | Configuración contradictoria; funciona por la vía de PostCSS | Unificar en una versión |
| 4 | `package.json` solo expone `build` y `dev` | No hay scripts de lint ni de pruebas de frontend | Añadir si crece el proyecto |
| 5 | La lista `modulosDisponibles` de usuarios y empleados omite `salidas` | No se puede asignar ese módulo desde la interfaz | Agregar `salidas` a la lista |
| 6 | `ModuloMiddleware` solo inspecciona el primer rol | Un usuario con varios roles se evalúa por uno | Validar un solo rol por usuario |
| 7 | Sin `Policies`, `Gates`, `Observers` ni eventos | El control de acceso vive en middleware | Evaluado al crecer el dominio |
| 8 | Sanctum instalado sin `guards.api` | Dependencia sin uso | Retirar si no habrá API |

### 12.1 Observación sobre la carga de datos

`ProductVariantController` permite registrar una variante con `stock` inicial. Ese valor **no genera asiento en Kardex**, por lo que una variante creada desde la interfaz queda descuadrada respecto del libro de existencias hasta que se registre un movimiento.

El catálogo sembrado se crea con `stock = 0`, de modo que el seed no sufre este efecto. Para introducir existencias iniciales conviene hacerlo mediante un ingreso real, que sí deja trazabilidad.

---

*Documento generado a partir del código fuente. Cualquier afirmación sobre conteos, rutas o métodos es verificable con `php artisan route:list`, `php artisan test` y la consulta de integridad de la sección 10.3.*