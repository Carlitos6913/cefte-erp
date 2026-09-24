# D-002 - Gestión de Inventario mediante Kardex

**Fecha:** 04/08/2026

## Decisión

El sistema no actualizará el stock directamente.

Cada ingreso o salida generará un movimiento en la tabla
`movimientos_inventario`, permitiendo mantener un historial completo de cambios.

## Justificación

- Trazabilidad.
- Auditoría.
- Reconstrucción del inventario.
- Soporte para reportes.
- Escalabilidad.

## Estado

Aceptada.
