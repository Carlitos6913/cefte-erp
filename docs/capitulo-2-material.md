# Capítulo 2 — Marco Teórico y Justificación

> **Insumo de trabajo para el Capítulo 2 de la tesis.**
> Documento preparado el 30/09/2026 a partir del código real del proyecto en
> `D:\Laragon\laragon\www\cefte-erp` (sistema **CEFTE**).
>
> Cada sección indica **qué escribir**, **con qué evidencia** y **qué no afirmar**.
> Los datos entre `[ ]` son los que debes completar o confirmar.

---

## Cómo usar este documento

Este archivo es un **andamiaje de escritura**, no el capítulo terminado. Antes de
cada sección tienes:

- **Qué escribir** — el contenido que la sección debe desarrollar.
- **Evidencia disponible** — los datos verificados del proyecto que puedes citar.
- **Fuentes a consultar** — lo que debes buscar para fundamentar.
- **Advertencia** — afirmaciones que no debes hacer sin respaldo.

Cifras técnicas tomadas directamente del proyecto:

| Elemento | Versión exacta | Fuente |
|---|---|---|
| PHP | 8.3.30 | `php -v` |
| Laravel Framework | v13.23.0 | `artisan --version` |
| inertiajs/inertia-laravel | v2.0.24 | `composer.lock` |
| inertiajs/vue3 (npm) | 2.3.27 | `package-lock.json` |
| vue | 3.5.40 | `package-lock.json` |
| vite | 8.2.0 | `package-lock.json` |
| @vitejs/plugin-vue | 6.0.8 | `package-lock.json` |
| tailwindcss | 3.4.19 | `package-lock.json` |
| @tailwindcss/vite | 4.3.3 | `package-lock.json` |
| postcss | 8.5.25 | `package-lock.json` |
| autoprefixer | 10.5.4 | `package-lock.json` |
| laravel-vite-plugin | 3.1.3 | `package-lock.json` |
| lodash | 4.18.1 | `package-lock.json` |
| spatie/laravel-permission | 8.3.0 | `composer.lock` |
| phpunit/phpunit | 12.5.33 | `composer.lock` |
| laravel/pint | `^1.27` | `composer.json` |
| Base de datos (prod.) | MySQL 8, base `cefte_erp` | `.env` |

> **Advertencia sobre Tailwind.** El proyecto declara `tailwindcss ^3.2.1` y
> `@tailwindcss/vite ^4.0.0` a la vez. La versión **instalada y en uso** es
> **3.4.19** (vía PostCSS). Si escribes "Tailwind 4", será incorrecto.

---

## Índice del capítulo

1. [2.1. Planteamiento del problema](#21-planteamiento-del-problema)
2. [2.2. Antecedentes](#22-antecedentes)
3. [2.3. Marco referencial](#23-marco-referencial)
4. [2.4. Análisis de sistemas existentes](#24-análisis-de-sistemas-existentes)
5. [2.5. Justificación de las herramientas](#25-justificación-de-las-herramientas)
6. [2.6. Propósito y objetivos](#26-propósito-y-objetivos)
7. [2.7. Alcance del sistema](#27-alcance-del-sistema)
8. [2.8. Marco metodológico](#28-marco-metodológico)
9. [2.9. Planificación](#29-planificación)
10. [2.10. Plasmación del UML a código](#210-plasmación-del-uml-a-código)

---

## 2.1. Planteamiento del problema

### Qué escribir

Descripción del problema **concreto y verificable** en la planta de procesamiento
de alimentos. Recorre la cadena de eventos y señala dónde se pierde el control:

1. Recepción de mercadería sin responsable registrado de forma confiable.
2. Registro de existencias en cuaderno o planilla, sin trazabilidad.
3. Riesgo de despacho de mercadería que ya fue vendida.
4. Venta sin salida autorizada.
5. Imposibilidad de saber qué se vendió a crédito y cuánto está pendiente.
6. Cálculo manual de mínimos de stock.

### Evidencia disponible

El sistema ya resolvió cada punto, y el código lo demuestra:

| Problema | Cómo lo resolvió el sistema | Evidencia |
|---|---|---|
| Responsable de recepción no confiable | Campo `ingresos.encargado_id` **obligatorio** con llave foránea a `employees`; el texto libre fue eliminado | Migración `2026_09_30_000001_add_encargado_to_ingresos_table.php`; 9 pruebas de regresión |
| Stock sin trazabilidad | Kardex `movimientos_inventario` con `stock_anterior` y `stock_nuevo` en cada asiento | `InventarioService`; `KardexTest` (5 pruebas) |
| Venta sin despacho | Saldo autorizado: solo salidas `motivo = 'venta'` habilitan vender | `SalidaVentaService`; `FlujoSalidaVentaTest` (9 pruebas) |
| Saldo de crédito desconocido | `ventas.saldo_pendiente` más tabla de `pagos` | `EdicionVentaPagosTest` (3 pruebas) |
| Mínimos de stock no controlables | `product_variants.minimum_stock` con consulta `whereColumn('stock','<=','minimum_stock')` | `DashboardController` |
| Acceso sin restricción clara | Control por rol **y** por módulo | `ModuloMiddleware`, `RoleMiddleware`; 10 pruebas |

### Fuentes a consultar

Normativa boliviana de higiene y seguridad alimentaria que exija trazabilidad de
lotes y registro de responsables. Verifica con la unidad ACME o el servicio de
sanidad si la planta está sujeta a una norma concreta; cita solo lo que confirmes.

### Advertencia

No afirmes que hubo "pérdidas económicas cuantificadas" si no tienes cifras de la
planta. Si no las tienes, usa argumentos cualitativos y di explícitamente que no
se realizó medición financiera del impacto.

---

## 2.2. Antecedentes

### Qué escribir

Cronología de cómo se han resuelto estos problemas en otras organizaciones y por
qué se llegó a la decisión de
desarrollar uno propio. Distingue entre sistemas de uso general y los del rubro.

### Fuentes a consultar

| Tipo | Dónde buscar | Para qué |
|---|---|---|
| Tesis y trabajos Bolivianos | Repositorios de la Universidad Mayor de San Andrés, Universidad Privada de Santa Cruz de la Sierra | Antecedentes directos, mismo rubro y país |
| Sistemas de manufactura | Sitios oficiales: Odoo, Dolibarr, ERPNext, BakeSoft | Comparar alcance y modelo de licencia |
| Sistemas bajo medida | Casos publicados por proveedores de software de gestión | Argumentar la opción de desarrollo propio |
| Aplicaciones de inventario | Play Store / App Store / npm | Mostrar que las móviles no cubren despacho-venta |

### Estructura sugerida

- **Antecedentes internacionales:** cómo la industria resolvió esto (SAP Business
  One, Fishbowl, Epicor).
- **Antecedentes nacionales:** sistemas usados por empresas bolivianas del rubro.
- **Antecedentes institucionales:** Marco Normativo de la throats institución `CEFTE`.
- **Trabajos previos de la carrera:** proyectos de grado similares.

### Advertencia

Distingue claramente entre "sistemas existentes" y "trabajos previos de la
carrera". Son doskarysections de tu capítulo.

---

## 2.3. Marco referencial

### Qué escribir

Definicionesepalabras clave del dominio, no del framework. El marco teórico va
primero: el lector debe saber qué es un inventario y un Kardex antes de saber que
existe Laravel.

### 2.3.1 Inventario y existencias

Contenido sugerido:

- Qué es inventario según la Contabilidad de Costos.
- Diferencia entre **inventario permanente** y **inventario periódico**.
- Por qué este sistema adoptó **permanente**: permite conocer el saldo en cualquier
  momento y fue condición para autorizar la venta.
- Existencias de materia prima, productos en proceso y productos terminados. El
  sistema solo cubre las dos últimas.

### 2.3.2 Kardex

Contenido sugerido:

- Definición de Kardex y su origen contable.
- Tarjeta kardex: una ficha por artículo, con entradas, salidas y saldo.
- Ventajas: trazabilidad, auditoría, reconstrucción del inventario.
- En el sistema el Kardex es una **tabla**, no una ficha física
  (`movimientos_inventario`), lo que permite consultar por cualquier criterio.

### 2.3.3 Costeo de inventario

- Métodos: promedio ponderado, primera entrada primera salida (FIFO), última
  entrada primera salida (LIFO), identificación específica.
- El sistema usa **promedio simple** para el precio de referencia de la variante.
  No implementa valoración contable.
- Aclaración importante: el "FIFO" del sistema (`SalidaVentaService::consumir`)
  **no es FIFO de valoración**. Ordena salidas por fecha para consumir el saldo
  autorizado más antiguo; no calcula costo.

> **Advertencia.** No escribas "el sistema usa FIFO para costear inventarios". Es
> falso. Verifica el código de `SalidaVentaService` si dudas.

### 2.3.4 Transacciones comerciales

- Venta al contado y venta a crédito.
- Saldo pendiente y su cálculo.
- Pagos parciales y su aplicación.
- En el sistema: `ventas.pago_tipo`, `ventas.saldo_pendiente`, tabla `pagos`.

### 2.3.5 Control de acceso

- Concepto de **mínimo privilegio**.
- Autenticación vs. **autorización**.
- RBAC (control de acceso basado en roles).
- Separación entre **rol** (rol de la persona) y **módulo** (área del sistema).
- En el sistema la autorización usa dos capas: `RoleMiddleware` y `ModuloMiddleware`.

### 2.3.6 Seguridad de la información

- Confidencialidad, integridad, disponibilidad.
- Hashing de contraseñas y sales.
- Gestión de sesión y cierre automático.
- En el sistema: bcrypt (`BCRYPT_ROUNDS=12`), verificación de correo, cambio
  obligatorio de contraseña (`must_change_password`).

### 2.3.7 Ingeniería de software

Contenido sugerido (el lector de tesis espera estos conceptos):

- Modelo **cliente-servidor** de tres capas (presentación, lógica de negocio, datos).
- Patrón **Modelo-Vista-Controlador** y su equivalente en Laravel.
- **Modelo-Vista-Presentador (MVP)**, que es el patrón real del proyecto.
- Patrón **Inyección de Dependencias**.
- **Migraciones** de base de datos y su reversibilidad.
- **Arquitectura server-driven** y por qué reduce la duplicación de validación.
- **Control de versiones** con Git.
- Metodologías ágiles: Scrum, Kanban. Si usaste Scrum, di qué sprint, qué historias
  de usuario y qué ceremonies.

### Fuentes a consultar

- Somerville, *Ingeniería de Software* — capas, MVC/MVP, patrones.
- Pressman & Maxim — arquitectura de software, modelos de proceso.
- Berdyyev — document ($Tesis de$) repositorios de la carrera para citar definiciones.
- Plan de estudio de tu carrera para confirmar qué teorías exigen.

### Advertencia

Si el plan de estudio de tu carrera pide Referencias Bibliográficas en formato
IEEE o APA, cítalo aquí. No inventes autores ni años.

---

## 2.4. Análisis de sistemas existentes

### Qué escribir

Comparación de lo que ya existe frente a lo que necesitas. La tabla comparativa es
lo más valioso de esta sección.

### 2.4.1 Categorías a comparar

| Categoría | Ejemplos | Qué evaluar |
|---|---|---|
| Sistemas de manufactura | SAP Business One, Fishbowl Inventory, Epicor | Alcance, costo, localised al rubro |
| Sistemas SMB / web | Dolibarr, ERPNext, Odoo Community | Costo de licencias, módulos incluidos |
| Sistemas sectoriales | BakeSoft | Diseñado para panadería y rubro afín |
| Software a medida | Desarrollado por proveedores | Costo total, tiempo, propiedad del código |
| Planillas | Excel / Google Sheets | Realidad actual de muchas SMEs |
| Aplicaciones móviles | Aplicaciones de inventario en tiendas de apps | Cobertura del flujo despacho-venta |

### 2.4.2 Tabla comparativa

Construye una tabla con estas filas y cuatro columnas de sistemas, más una quinta
columna "Sistema propuesto". Criterios sugeridos:

| Criterio | Sistema A | Sistema B | Sistema C | Sistema propuesto |
|---|---|---|---|---|
| Costo de adquisición | | | | 0 (desarrollo propio) |
| Costo de licencia anual | | | | 0 |
| Kardex con trazabilidad | | | | Sí |
| Responsable de ingreso en padrón | | | | Sí |
| Venta condicionada a despacho | | | | Sí |
| Saldo pendiente y pagos | | | | Sí |
| Control por rol y módulo | | | | Sí |
| Adaptado a alimentos | | | | Sí |
| Idioma de interfaz en español | | | | Sí |
| Soporte en Bolivia | | | | — |
| Curva de aprendizaje | | | | — |

**Datos del sistema propuesto que ya tienes verificados:**

- Kardex: `movimientos_inventario`, 96 asientos en la carga de demostración.
- Responsable: `encargado_id` NOT NULL + FK, 30 de 30 ingresos con responsable.
- Venta condicionada: `SalidaVentaService::consumir`, 9 pruebas.
- Rol y módulo: `ModuloMiddleware` (12 módulos), `RoleMiddleware` (2 niveles).
- Español: identificadores, rutas, validaciones e interfaz íntegramente en español.

### 2.4.3 Conclusión de la sección

Enmarca por qué un desarrollo propio se justifica:

- Los productos sectoriales están pensados para panadería, no para conserva y
  lácteos.
- Las planillas no garantizan integridad referencial ni trazabilidad.
- Un desarrollo propio se ajusta al flujo real de la planta y deja código
  propiedad de la institución.

### Advertencia

Verifica cada celda de la tabla con la documentación oficial del producto que
analices. No completes por memoria: la jury puede pedir fuentes.

---

## 2.5. Justificación de las herramientas

### Qué escribir

Por qué Laravel, por qué Vue, por qué MySQL, por qué Inertia. La comparación debe
ser argumentada, no una lista de ventajas.

### 2.5.1 Comparativa de frameworks backend

| Criterio | Laravel 13 | Symfony 7 | Django 5 | CodeIgniter 4 |
|---|---|---|---|---|
| Lenguaje | PHP 8.3 | PHP 8.3 | Python 3.12 | PHP 8.3 |
| Curva de aprendizaje | Baja | Media-Alta | Media | Baja |
| ORM integrado | Eloquent | Doctrine | Django ORM | Query Builder |
| Migraciones | Sí | Sí | Sí | Sí |
| Ecosistema de paquetes | Muy amplio | Amplio | Amplio | Limitado |
| Soporte oficial en la comunidad | Muy grande | Grande | Muy grande | Mediana |
| Adecuado para este proyecto | Sí | Sí, con más configuración | Sí | Sí, con menos Control Structure |

**Argumento central sugerido:** el proyecto se ejecuta en PHP 8.3, el lenguaje con
mayor disponibilidad de hosting compartido en el país. Laravel ofrece un
ecosistema maduro con resolución de dependencias por contenedor, lo que permite
encapsular las reglas de negocio en servicios (`InventarioService`,
`SalidaVentaService`, `NumeradorService`, `CodigoService`, `UserService`) y
probarlas de forma aislada.

### 2.5.2 Comparativa de frameworks frontend

| Criterio | Vue 3 + Inertia | React + Next.js | Angular | jQuery clásico |
|---|---|---|---|---|
| Curva de aprendizaje | Baja | Media | Alta | Baja |
| Performance | Alta | Alta | Media-Alta | Baja |
| Sistema de componentes | Propio | Propio | Propio | Ninguno nativo |
| Renderizado | Cliente | Mixto (SSR) | Cliente | Cliente |
| Integración con Laravel | Nativa | Requiere capa API | Requiere capa API | Manual |
| Código duplicado cliente/servidor | No | Sí | Sí | Sí |

**Argumento clave:** Inertia.js elimina la duplicación del control de validación.
La misma definición de reglas viaja del controlador al componente, de modo que el
mensaje de error se muestra sin reescribir la regla en JavaScript.

### 2.5.3 Comparativa de bases de datos

| Criterio | MySQL 8 | PostgreSQL 17 | SQL Server | SQLite |
|---|---|---|---|---|
| Licencia | GPL (Community) | PostgreSQL License | Comercial (Developer) | Dominio público |
| Disponibilidad en hosting | Muy alta | Media | Media | Incluida |
| ACID | Sí | Sí | Sí | Sí |
| Control de concurrencia | Bloqueo por fila | MVCC | Bloqueo/opt. | Limitado |
| Adecuado para este proyecto | Sí | Sí | Sobredimensionado | Solo pruebas |

> **Nota relevante.** El sistema usa **SQLite en memoria** exclusivamente para las
> 80 pruebas automatizadas, y **MySQL 8** en producción y desarrollo. Esta
> distinción es una decisión de diseño y puedes presentarla como tal: las
> pruebas son rápidas y aisladas, la operación usa un motor concurrente real.

### 2.5.4 Tabla resumen de la decisión

| Capa | Tecnología | Versión | Justificación en una frase |
|---|---|---|---|
| Backend | Laravel | 13.23.0 | Ecosistema maduro y resolución de dependencias por contenedor. |
| Lenguaje | PHP | 8.3.30 | Máxima disponibilidad de hosting en el contexto del proyecto. |
| Frontend | Vue | 3.5.40 | Curva de aprendizaje baja y rendimiento adecuado. |
| Integración | Inertia.js | 2.0.24 / 2.3.27 | Evita duplicar validaciones entre servidor y cliente. |
| Estilos | Tailwind CSS | 3.4.19 | Consistencia visual sin CSS personalizado extenso. |
| Build | Vite | 8.2.0 | Compilación rápida conHot Module Replacement. |
| Base de datos | MySQL | 8 | Transacciones y bloqueo de fila por `SELECT ... FOR UPDATE`. |
| Control de acceso | Spatie Permission | 8.3.0 | Roles y permisos sobre tablas existentes en el proyecto. |
| Pruebas | PHPUnit | 12.5.33 | Suite integrada al framework Artisan. |
| Estilo de código | Laravel Pint | ^1.27 | Formato automático y consistente. |
| Autenticación | Sesión nativa | — | Suficiente para aplicación interna; evita complejidad de tokens. |

### Fuentes a consultar

- Documentación oficial de cada herramienta.
- Historial de versiones para justificar la elección de la versión actual.
- Comparativas de rendimiento si tu plan lo exige.

### Advertencia

La tabla de frameworks frontend menciona "Código duplicado cliente/servidor". Eso
se aplica a React + Next o Angular con una API aparte; Inertia no aplica
validaciones duplicadas. Precisa la redacción para que sea técnicamente exacto.

---

## 2.6. Propósito y objetivos

### Qué escribir

Una sola frase de propósito, luego objetivos con verbo medible.

### Propósito

> Propósito: desarrollar e implantar un sistema web de gestión de inventario,
> ventas y cobranza para el centro de procesamiento de alimentos `CEFTE`, que
> garantice la trazabilidad de las existencias y la trazabilidad de cada ingreso.

### Objetivo general

> Desarrollar e implantar un sistema web que automatice la gestión de inventario,
> la comercialización y la cobranza del centro de procesamiento de alimentos,
> garantizando la trazabilidad de las existencias y el control del acceso según
> rol.

### Objetivos específicos

| # | Objetivo específico | Cómo se cumple (evidencia del código) |
|---|---|---|
| 1 | Modelar el catálogo de productos y sus presentaciones | Tablas `categories`, `products`, `product_variants` con SKU automático |
| 2 | Registrar ingresos con responsable asignado del padrón de empleados | `ingresos.encargado_id` NOT NULL + FK; 9 pruebas |
| 3 | Mantener un Kardex que refleje cada movimiento de existencias | `InventarioService` con `stock_anterior` y `stock_nuevo` |
| 4 | Impedir el despacho de mercadería inexistente | `lockForUpdate()` y excepción si el saldo es negativo |
| 5 | Impedir la venta de mercadería no despachada | Saldo autorizado FIFO en `SalidaVentaService` |
| 6 | Administrar ventas al contado y a crédito con saldo pendiente | `ventas.pago_tipo`, `saldo_pendiente`, tabla `pagos` |
| 7 | Aplicar pagos parciales y totales sobre las ventas | `EdicionVentaPagosTest` (3 pruebas) |
| 8 | Restringir el acceso según rol y módulo | `RoleMiddleware` + `ModuloMiddleware` |
| 9 | Generar reportes de indicadores operativos | `DashboardController` e `InformesController` |
| 10 | Garantizar la calidad mediante pruebas automatizadas | 80 pruebas, 285 aserciones, todas aprobadas |

### Advertencia

Los objetivos deben ser **medibles**. Si tu institución exige que el objetivo
específico tenga un verbo en infinitivo y sea verificable, revísalos. El objetivo 3
dice "que refleje"; podrías strengthenlo a "que registre la existencia anterior y
posterior de cada variante".

---

## 2.7. Alcance del sistema

### Qué escribir

Delimitar **qué entra** y **qué no entra**. Es la sección que evita juria en la
defensa.

### 2.7.1 Dentro del alcance

| Módulo | Funciones |
|---|---|
| Autenticación | Inicio de sesión, cierre, restablecimiento, cambio obligatorio de contraseña |
| Seguridad | Control por rol y por módulo, verificación de correo |
| Personal | Padrón de empleados, cuentas de usuario, vínculo usuario-empleado |
| Catálogo | Categorías, unidades de medida, productos, variantes con SKU automático |
| Inventario | Ingresos con responsable, salidas (despacho y merma), Kardex, anulaciones |
| Comercial | Clientes, ventas al contado y a crédito, pagos |
| Reportes | Panel de indicadores, informes por rango de fechas, alertas de stock mínimo |
| Documentación | Correlativos y códigos consecutivos |

### 2.7.2 Fuera del alcance

Be explicit. Esto **resta** puntos si no lo declaras:

- Gestión de compras y cuentas por pagar.
- Facturación electrónica y facturación fiscal.
- Control de producción: recetas, lotes,.traceabilidad de materia prima.
- Costeo de inventario por valoración (FIFO, LIFO, promedio ponderado real).
- Nómina y recursos humanos.
- Integración con lectores de código de barras.
- Aplicación móvil nativa.
- Integración con bancos o pasarelas de pago.

### 2.7.3 Usuarios del sistema

| Rol | Módulos asignados | Operaciones reservadas |
|---|---|---|
| `Administrador` | Acceso total | Todo, incluida la eliminación de catálogo y personal |
| `Gerente` | Acceso total | Validación de operaciones; anulación de ingresos, salidas y ventas |
| `Almacenero` | `categorias`, `unidades`, `productos`, `variantes`, `ingresos`, `salidas`, `informes` | Registro físico y digital de movimientos |
| `Vendedor` | `productos`, `variantes`, `clientes`, `ventas`, `pagos` | Venta y cobranza |

> Estos cuatro roles son los definidos en `RolesYPermisosSeeder`. Los roles
> Administrador y Gerente guardan `modulos = NULL` (acceso total). Hay una prueba
> de regresión que impide que cambien a lista vacía: `ModulosPorRolTest`.

### 2.7.4 Estados del sistema

Para la defensa, aclara que el sistema está en fase:

- [ ] **Desarrollo** (es lo vigente: 80 pruebas en verde)
- [ ] **Pruebas de usuario** — pendiente con personal de la planta
- [ ] **Puesta en marcha** — pendiente
- [ ] **Mantenimiento** — pendiente

---

## 2.8. Marco metodológico

### Qué escribir

Los métodos y las herramientas con las que construiste el sistema. La jury
pregunta esto con frecuencia.

### 2.8.1 Metodología de desarrollo

Si usaste Scrum, completa esta tabla:

| Elemento | Detalle a completar |
|---|---|
| Marco de trabajo | Scrum / Kanban / otro |
| Duración total | `[N]` sprints de `[X]` semanas |
| Duración de cada sprint | `[X]` semanas |
| Roles | Product Owner, Scrum Master, equipo de desarrollo |
| Ceremonias usadas | Planeación, daily, revisión, retrospectiva |
| Artefactos | Product backlog, sprint backlog, increment |
| Herramienta de tablero | ¿Jira, Trello, GitHub Projects? |
| Herramienta de control de versiones | Git (confirmar y documentar) |

Si no usaste una metodología ágil formal, dilo con franqueza y describe el
ciclo real: análisis, modelado, migraciones, controladores, vistas, pruebas,
documentación. **No inventes ceremonias que no realizaste.**

### 2.8.2 Herramientas

| Categoría | Herramienta | Versión | Uso en el proyecto |
|---|---|---|---|
| Lenguaje | PHP | 8.3.30 | Backend |
| Framework backend | Laravel | 13.23.0 | Estructura, ORM, rutas, validación |
| Frontend | Vue | 3.5.40 | Interfaz |
| Integración | Inertia.js | 2.0.24 / 2.3.27 | Puente servidor-cliente |
| Base de datos | MySQL | 8 | Persistencia |
| ORM | Eloquent | (Laravel) | Acceso a datos |
| Migraciones | Laravel Migrations | (Laravel) | 34 migraciones reversibles |
| Control de versiones | Git | `[completar]` | Historial de código |
| Pruebas | PHPUnit | 12.5.33 | 80 pruebas automatizadas |
| Formato de código | Laravel Pint | ^1.27 | Estándar PSR-12 |
| Compilación | Vite | 8.2.0 | Bundling del frontend |
| Estilos | Tailwind CSS | 3.4.19 | Estilos de interfaz |
| Consola | Laravel Tinker | ^3.0 | Consultas y depuración |

### 2.8.3 Técnicas de modelado

- **Modelo conceptual:** diagrama entidad-relación (ya documentado en
  `docs/capitulo.md`, sección 4.1).
- **Modelo lógico:** migraciones que definen el esquema.
- **Modelo físico:** tipo de dato y longitud de cada columna, índices y llaves.

### 2.8.4 Técnicas de construcción

Indica cómo aplicaste:

- Programación orientada a objetos (composición en controladores).
- Inyección de dependencias.
- Respaldo de servicios de dominio.
- Validación en el servidor con mensajes en español.
- Llevar la lógica al servidor (arquitectura server-driven).
- Pruebas antes de la corrección (TDD) — solo si es cierto.

### 2.8.5 Técnicas de verificación

| Tipo | Qué se verificó | Evidencia |
|---|---|---|
| Pruebas unitarias | Funciones aisladas | `tests/Unit` |
| Pruebas funcionales | Flujos HTTP completos | 80 pruebas, 285 aserciones |
| Pruebas de esquema | Nulabilidad y llaves foráneas | `IngresoEncargadoMigrationTest` (4 pruebas) |
| Verificación de estilo | Conformidad de formato | `vendor/bin/pint --test` en verde |
| Verificación de compilación | Build del frontend | `npm run build` en verde |
| Verificación de integridad | Stock contra Kardex | Consulta SQL de la sección 10.3 de `docs/capitulo.md` |

---

## 2.9. Planificación

### Qué escribir

Cronograma de las fases del proyecto. La tabla del calendario de migraciones que
ya tienes es una evidencia sólida.

### 2.9.1 Fases sugeridas

| Fase | Actividades | Producto |
|---|---|---|
| 1. Levantar requirements | Reuniones con la planta, identificación de roles, procesos | Documento de requisitos |
| 2. Modelar | Diagrama entidad-relación, diccionario de datos | Esquema normalizado |
| 3. Construir base | 34 migraciones, seeders de demostración | Base de datos poblada |
| 4. Implementar backend | Modelos, controladores, servicios | API interna completa |
| 5. Implementar frontend | 52 páginas Vue, componentes de interfaz | Interfaz navegable |
| 6. Asegurar acceso | Middleware de rol y módulo, autenticación | Control de acceso |
| 7. Probar | Suite automatizada, pruebas manuales con usuarios | Informe de pruebas |
| 8. Documentar | Manuales de usuario y técnico | Documentación |

### 2.9.2 Cronología del esquema

Esta tabla ya está verificada en el proyecto; transcribe la sección 4.9 de
`docs/capitulo.md` o el archivo `docs/capitulo-3.md`.

### 2.9.3 Diagrama Gantt

Si tu plan exige cronograma visual, constrúyelo con las fases anteriores. Puedes
usar una tabla de markdown o exportarlo desde una herramienta.

### Advertencia

Las fechas de las fases deben coincidir con tu bitácora real. La tabla de
migraciones refleja el desarrollo real; la de fases es tu interpretación.

---

## 2.10. Plasmación del UML a código

### Qué escribir

Esta es la sección más importante: **demuestra que lo diseñado existe**. Debes
cruzar los diagramas UML con archivos reales del proyecto.

### 2.10.1 Correspondencia diagramas → código

| Diagrama UML | Artefacto | Dónde está en el código |
|---|---|---|
| Diagrama de clases | Modelos Eloquent | `app/Models/*.php` (21 modelos) |
| Diagrama de secuencia de ingreso | Flujo de creación de ingreso | `IngresoController@store` + `InventarioService` |
| Diagrama de secuencia de venta | Flujo de venta autorizada | `VentaController@store` + `SalidaVentaService` |
| Diagrama de casos de uso | Módulos y rutas | `routes/web.php` + `php artisan route:list` |
| Diagrama de actividades | Proceso de recepción | `IngresoController`, `IngresosSeeder` |
| Modelo de datos | Esquema físico | 34 migraciones en `database/migrations` |
| Máquina de estados | Estados de los documentos | Columna `estado` en `ingresos`, `salidas`, `ventas` |

### 2.10.2 Cómo explicar un caso

Ejemplo de redacción para el registro de un ingreso:

> **Caso: Registrar ingreso de mercadería.**
> El usuario `Almacenero` accede a `/ingresos/create` y el sistema muestra el
> formulario con el catálogo de variantes activas y el padrón de empleados
> activos. Al enviar, `IngresoController@store` valida la entrada con la regla
> `encargado_id` requerida y existente en `employees`. Luego, dentro de una
> transacción (`DB::transaction`), obtiene el correlativo `ING-2026-000001` de
> `NumeradorService`, crea la cabecera del ingreso, inserta las líneas de detalle
> y por cada línea invoca `InventarioService::registrarMovimiento`, que bloquea
> la variante con `lockForUpdate()`, calcula el nuevo stock y escribe el asiento
> en `movimientos_inventario` con `stock_anterior` y `stock_nuevo`. Si el stock
> quedara negativo, la excepción revierte toda la transacción.

### 2.10.3 Casos de uso con su código

| Caso de uso | Actor | Ruta | Método | Servicio asociado |
|---|---|---|---|---|
| Iniciar sesión | Visitante | `POST /login` | `AuthenticatedSessionController@store` | — |
| Cambiar contraseña | Cualquier usuario | `PUT /password/cambio` | `PasswordCambioController@update` | — |
| Registrar ingreso | `Almacenero` | `POST /ingresos` | `IngresoController@store` | `NumeradorService`, `InventarioService` |
| Anular ingreso | `Administrador`, `Gerente` | `DELETE /ingresos/{id}` | `IngresoController@destroy` | `InventarioService::revertir` |
| Registrar salida | `Almacenero` | `POST /salidas` | `SalidaController@store` | `NumeradorService`, `InventarioService` |
| Registrar venta | `Vendedor` | `POST /ventas` | `VentaController@store` | `NumeradorService`, `SalidaVentaService` |
| Registrar pago | `Vendedor` | `POST /pagos` | `PagoController@store` | — |
| Crear variante | `Almacenero` | `POST /variantes` | `ProductVariantController@store` | `CodigoService` |
| Crear usuario | `Administrador` | `POST /usuarios` | `UserController@store` | `UserService` |
| Consultar panel | Todos | `GET /dashboard` | `DashboardController` | — |
| Consultar informes | `Almacenero`, `Gerente` | `GET /informes` | `InformesController` | — |

### 2.10.4 Extensión del modelo

El diagrama de clases y el código deben coincidir. Cambios recientes que debes
reflejar en tus diagramas:

| Cambio | Efecto en el diagrama |
|---|---|
| `ingresos.proveedor` (texto) eliminada | La clase `Ingreso` ya no tiene ese atributo |
| `ingresos.encargado_id` agregada | Nueva asociación `Ingreso (N) → Employee (1)`, multiplicity obligatoria |
| Tabla `secuencias` creada | Nueva entidad `Secuencia` |
| `salida_detalles.cantidad_vendida` | Ya aparece en el diagrama de clases |

### Advertencia

Si tus diagramas UML no reflejan estos cuatro cambios, la jury lo notará. Son
modificaciones posteriores al diseño original y debes actualizar los diagramas.

---

## Material de apoyo

### Documentos del proyecto que puedes citar

| Archivo | Contenido útil |
|---|---|
| `docs/capitulo.md` | Documentación técnica completa del sistema (arquitectura, modelo de datos, servicios, pruebas, deuda técnica) |
| `docs/capitulo-3.md` | Capítulo 3 redactado, con tablas de pruebas y cronograma de migraciones |
| `docs/decisiones.md` | Decisiones de arquitectura registradas (ej. D-002: Kardex) |

### Comandos para citar evidencia

```bash
# Versiones exactas
php artisan --version                  # Laravel Framework 13.23.0
php -v                                # PHP 8.3.30

# Rutas y middleware (para el diagrama de casos de uso)
php artisan route:list

# Pruebas (para el capítulo de verificación)
php artisan test

# Estilo de código
vendor/bin/pint --test

# Compilación del frontend
npm run build

# Esquema aplicado
php artisan migrate:status

# Integridad del inventario contra el Kardex
# (consulta en docs/capitulo.md, sección 10.3)
```

### Cifras verificadas del proyecto

| Dato | Valor | Dónde verificarlo |
|---|---|---|
| Migraciones | 34 | `database/migrations` |
| Modelos | 21 | `app/Models` |
| Servicios de dominio | 5 | `app/Services` |
| Middleware propio | 4 | `app/Http/Middleware` |
| Páginas Vue | 52 | `resources/js/Pages` |
| Componentes Vue (total) | 78 | `resources/js` |
| Pruebas automatizadas | 80 | `php artisan test` |
| Aserciones | 285 | `php artisan test` |
| Archivos de prueba | 19 | `tests` |
| Seeders activos | 7 | `DatabaseSeeder` |
| Roles definidos | 4 | `RolesYPermisosSeeder` |
| Módulos protegidos | 12 | `routes/web.php` |
| Asientos de Kardex (demo) | 96 | Base `cefte_erp` |

---

## Advertencias finales

1. **No inventes datos de la planta.** Si no tienes cifras de pérdidas, mermas o
   costos, usa argumentos cualitativos y dilo explícitamente.
2. **No inventes metodología.** Si no usaste Scrum formalmente, describe el ciclo
   real de desarrollo.
3. **Verifica cada celda de la tabla comparativa** con documentación oficial.
4. **Actualiza tus diagramas UML** con los cuatro cambios listados en 2.10.4.
5. **Distingue FIFO de saldo autorizado** de FIFO de costeo (ver 2.3.3).
6. **Corrige Tailwind**: la versión en uso es 3.4.19, no 4.
7. **Declara el estado real del proyecto** (ver 2.7.4). No lo presentes como
   terminado si aún está en desarrollo.
8. **Cita las fuentes en el formato que exige tu institución** (IEEE, APA o el que
   corresponda).
