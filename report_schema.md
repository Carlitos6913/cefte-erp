# Inventario de Esquema de Base de Datos - Proyecto CEFTE

Proyecto: CEFTE (Laravel)
Fecha: Mon Oct 05 2026


---

## 1. Listado de migraciones
| # | Nombre de archivo | Descripci�n |
|---|-------------------|-------------|
| 1 | 0001_01_01_000000_create_users_table.php | Crea tablas users, password_reset_tokens, sessions. |
| 2 | 0001_01_01_000001_create_cache_table.php | Crea tabla cache (para Laravel). |
| 3 | 0001_01_01_000002_create_jobs_table.php | Crea tablas jobs, job_batches, failed_jobs. |
| 4 | 2026_08_04_131148_create_permission_tables.php | Crea tablas Spatie Permission (permissions, roles, model_has_permissions, model_has_roles, role_has_permissions). |
| 5 | 2026_08_04_150259_add_deleted_at_to_users_table.php | Agrega soft delete a users (deleted_at). |
| 6 | 2026_08_08_075333_create_productos_table.php | Crea tabla productos (versi�n inicial). |
| 7 | 2026_08_08_083116_create_ingresos_table.php | Crea tabla ingresos. |
| 8 | 2026_08_08_083124_create_ingreso_detalles_table.php | Crea tabla ingreso_detalles. |
| 9 | 2026_08_10_143108_add_estado_to_ingresos_table.php | Agrega columna estado (default 'activo') a ingresos. |
| 10 | 2026_08_13_133720_create_categorias_table.php | Crea tabla categorias (versi�n inicial). |
| 11 | 2026_08_13_135730_add_categoria_id_to_productos_table.php | Agrega FK categoria_id a productos. |
| 12 | 2026_08_19_090000_create_ventas_table.php | Crea tabla ventas. |
| 13 | 2026_08_19_090100_create_venta_detalles_table.php | Crea tabla venta_detalles. |
| 14 | 2026_08_19_100000_create_clientes_table.php | Crea tabla clientes. |
| 15 | 2026_08_19_100100_add_pago_tipo_and_cliente_to_ventas_table.php | Agrega cliente_id (FK), pago_tipo, saldo_pendiente a ventas. |
| 16 | 2026_08_19_100200_create_pagos_table.php | Crea tabla pagos. |
| 17 | 2026_08_25_000000_add_activo_to_users_table.php | Agrega campo activo a users. |
| 18 | 2026_08_26_000000_add_user_fields_and_role_modulos.php | Agrega campos a users (apellido_paterno, materno, ci, telefono) y modulos a roles (json). |
| 19 | 2026_08_26_010000_create_salidas_table.php | Crea tabla salidas. |
| 20 | 2026_08_26_010001_create_salida_detalles_table.php | Crea tabla salida_detalles. |
| 21 | 2026_09_09_000000_add_direccion_to_users_table.php | Agrega direccion a users. |
| 22 | 2026_09_10_000000_create_employees_table.php | Crea tabla employees. |
| 23 | 2026_09_10_000001_modify_users_table.php | Migra datos de users hacia employees, agrega employee_id, renombra activo a is_active, elimina columnas migradas. |
| 24 | 2026_09_24_000001_create_catalog_tables.php | Crea nuevo cat�logo: categories, unit_measures, products, product_variants, y tablas de mapeo. |
| 25 | 2026_09_24_000002_migrate_catalog_data.php | Migra datos del cat�logo antiguo (productos/categorias/unidades) al nuevo. |
| 26 | 2026_09_24_000003_add_employee_foreign_key_to_users_table.php | Agrega FK users.employee_id -> employees.id (nullOnDelete). |
| 27 | 2026_09_25_000001_add_must_change_password_to_users_table.php | Agrega must_change_password (boolean default false) a users. |
| 28 | 2026_09_25_000002_create_correlativos_table.php | Crea tabla correlativos (codigo, anio, ultimo) con unique(codigo, anio). |
| 29 | 2026_09_25_000003_create_movimientos_inventario_table.php | Crea tabla movimientos_inventario (kardex) con �ndices y FK a product_variants, users. |
| 30 | 2026_09_25_000004_normalizar_product_variant_id_en_detalles.php | Normaliza detalles para usar product_variant_id en lugar de producto_id (con tratamiento por driver). |
| 31 | 2026_09_25_000005_add_cantidad_vendida_to_salida_detalles_table.php | Agrega cantidad_vendida a salida_detalles. |
| 32 | 2026_09_25_000006_create_salida_detalle_venta_detalle_table.php | Crea tabla pivot salida_detalle_venta_detalle. |
| 33 | 2026_09_30_000000_create_secuencias_table.php | Crea tabla secuencias y inicializa secuencia PROD desde productos. |
| 34 | 2026_09_30_000001_add_encargado_to_ingresos_table.php | Agrega encargado_id (FK employees) a ingresos, hace obligatorio tras migraci�n, elimina proveedor. |
| 35 | 2026_10_02_000001_add_unidades_a_product_variants.php | Agrega unidad_base_id, unidad_empaque_id, factor_empaque a product_variants; agrega unidad_id, cantidad_original a detalles y a movimientos_inventario. |


---

## 2. Tablas principales


### 2.1 users
- **Prop�sito (ES):** Usuarios del sistema para autenticaci�n y trazabilidad.
- **PK:** id (BIGINT, autoincrement)
- **Columnas (resumen):** id, employee_id, name, email, email_verified_at, password, is_active, must_change_password, remember_token, created_at, updated_at
- **FKs:** employee_id -> employees(id) (nullOnDelete).
- **�nicos:** email, employee_id.

### 2.2 employees
- **Prop�sito (ES):** Ficha de empleado (nombres, CI, contacto, profesi�n, estado).
- **PK:** id
- **Columnas:** first_name, first_surname, second_surname(NULL), identity_card(UNIQUE), phone(NULL), address(NULL text), profession(NULL), email(NULL UNIQUE), is_active(boolean default true), timestamps.
- **�nicos:** identity_card, email.

### 2.3 categories
- **Prop�sito (ES):** Categor�as de productos (nuevo cat�logo).
- **PK:** id; **�nicos:** code. Columnas: code(v20 UNIQUE), name(v100), description(NULL), timestamps.

### 2.4 categorias (antigua)
- **Prop�sito (ES):** Tabla antigua; migrada a categories (mapeo categorias_vieja_mapping).
- **Columnas:** nombre(UNIQUE), descripcion(NULL), activo(bool default true), timestamps.

### 2.5 productos (antigua)
- **Prop�sito (ES):** Tabla antigua migrada a products/product_variants. Incluye: codigo(UNIQUE), nombre, descripcion(NULL), unidad_medida, precio_compra, precio_venta, stock(int), stock_minimo(int), activo(bool), categoria_id(FK nullable -> categorias), timestamps. (Mapeo: productos_vieja_mapping)

### 2.6 unit_measures
- **Prop�sito (ES):** Unidades de medida.
- **PK:** id. Columnas: name(varchar50), symbol(varchar10), timestamps.

### 2.7 products
- **Prop�sito (ES):** Producto maestro (nuevo cat�logo).
- **PK:** id; **�nicos:** code. Columnas: category_id(FK nullable -> categories nullOnDelete), code(v30 UNIQUE), name(v150), description(NULL), is_active(bool default true), timestamps.
- **FKs:** category_id -> categories(id) (nullOnDelete).

### 2.8 product_variants
- **Prop�sito (ES):** Variantes de producto (presentaci�n/envase/sabor). Stock controlado en unidad base.
- **PK:** id. **�nico:** sku. **�ndice:** var_unidades_idx(unidad_base_id, unidad_empaque_id).
- **Columnas:** product_id(FK->products CASCADE), unit_measure_id(FK->unit_measures RESTRICT), unidad_base_id(FK->unit_measures RESTRICT NULL), unidad_empaque_id(FK->unit_measures RESTRICT NULL), factor_empaque(decimal10,2 NULL), sku(v50 UNIQUE), flavor(v100 NULL), packaging_type(v100 NULL), net_content(decimal8,2 NULL), stock(decimal10,2 default 0), minimum_stock(decimal10,2 default 0), unit_price(decimal10,2), is_active(bool default true), timestamps.
- **FKs:** product_id->products(id) CASCADE; unit_measure_id->unit_measures(id) RESTRICT; unidad_base_id->unit_measures(id) RESTRICT; unidad_empaque_id->unit_measures(id) RESTRICT.

### 2.9 clientes
- **Prop�sito (ES):** Clientes para ventas.
- **PK:** id; **�nico:** ci_nit (NULL). Columnas: nombre(v150), ci_nit(v30 UNIQUE NULL), telefono(v30 NULL), email(NULL), direccion(text NULL), activo(bool default true), timestamps.

### 2.10 ingresos
- **Prop�sito (ES):** Ingresos de mercader�a al almac�n.
- **PK:** id; **�nico:** numero(v30). Columnas: numero, fecha, encargado_id(FK->employees RESTRICT NOT NULL), observaciones(NULL), total(decimal12,2 default 0), estado(v20 default 'activo'), user_id(FK->users RESTRICT), timestamps.
- **FKs:** user_id->users RESTRICT; encargado_id->employees RESTRICT.

### 2.11 ingreso_detalles
- **Prop�sito (ES):** L�neas de ingreso.
- **PK:** id. Columnas: ingreso_id(FK CASCADE), product_variant_id(FK RESTRICT), cantidad(unsigned), unidad_id(FK unit_measures NULL nullOnDelete), cantidad_original(decimal10,2 NULL), precio_unitario(decimal12,2), subtotal(decimal12,2), timestamps.
- **FKs:** ingreso_id->ingresos CASCADE; product_variant_id->product_variants RESTRICT; unidad_id->unit_measures NULL ON DELETE.

### 2.12 ventas
- **PK:** id; **�nico:** numero(v30). Columnas: numero, fecha, cliente(NULL), cliente_id(FK->clientes NULL nullOnDelete), observaciones(NULL), total(decimal12,2 default 0), estado(v20 default 'activo'), pago_tipo(v20 default 'contado'), saldo_pendiente(decimal12,2 default 0), user_id(FK->users RESTRICT), timestamps.
- **FKs:** user_id->users RESTRICT; cliente_id->clientes NULL ON DELETE.

### 2.13 venta_detalles
- **PK:** id. Columnas: venta_id(FK CASCADE), product_variant_id(FK RESTRICT), cantidad(unsigned), unidad_id(FK unit_measures NULL nullOnDelete), cantidad_original(decimal10,2 NULL), precio_unitario(decimal12,2), subtotal(decimal12,2), timestamps.
- **FKs:** venta_id->ventas CASCADE; product_variant_id->product_variants RESTRICT; unidad_id->unit_measures NULL ON DELETE.

### 2.14 pagos
- **Prop�sito (ES):** Pagos realizados sobre ventas.
