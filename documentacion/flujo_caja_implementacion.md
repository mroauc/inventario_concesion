# Implementación: Módulo Flujo de Caja

**Fecha:** 2026-04-23 / 2026-04-26  
**Sistema:** Laravel 8 — Inventario Concesión  
**Rama:** main  

---

## Resumen

Se implementó un módulo nuevo e independiente de **Flujo de Caja** para el control del movimiento diario de dinero en el negocio. El módulo es completamente nuevo — no modifica ningún módulo existente (órdenes de servicio, inventario, etc.).

---

## Decisiones de diseño adoptadas

| Pregunta | Decisión |
|---|---|
| ¿Persistir o calcular cierres? | Persistir: el cierre se guarda al cerrar el día explícitamente |
| ¿Editar/eliminar movimientos? | Anulación (campo `anulado = true`), nunca eliminación física |
| ¿Multiusuario? | Caja por concesión; todos los usuarios operan la misma caja del día |
| ¿Cierre explícito o automático? | Botón de cierre, con opción de reabrir |
| ¿Depósito único o múltiple? | Múltiple: los depósitos son movimientos con medio `deposito_banco` |
| ¿Apertura Tecnoelectro? | Se carga desde el cierre del día hábil anterior; es editable |
| ¿Vinculado a órdenes de servicio? | No, son registros completamente independientes |

**Tecnoelectro** es el software de emisión de boletas/facturas electrónicas (SII). Tiene su propio subflujo dentro de la misma caja: apertura, movimientos, depósito y cierre paralelos al flujo de caja chica general.

---

## Archivos creados

### Migraciones

| Archivo | Descripción |
|---|---|
| `database/migrations/2026_04_23_000001_create_cajas_diarias_table.php` | Crea tabla `cajas_diarias` |
| `database/migrations/2026_04_23_000002_create_movimientos_caja_table.php` | Crea tabla `movimientos_caja` |

### Modelos

| Archivo | Descripción |
|---|---|
| `app/Models/CajaDiaria.php` | Modelo principal con métodos de cálculo de cierres y lógica de día hábil anterior |
| `app/Models/MovimientoCaja.php` | Modelo de movimientos con labels legibles y relaciones |

### Controller y vistas

| Archivo | Descripción |
|---|---|
| `app/Http/Controllers/FlujoCajaController.php` | Controller con 7 métodos (ver detalle abajo) |
| `resources/views/flujo_caja/index.blade.php` | Vista principal del módulo |

---

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `routes/web.php` | Grupo de rutas `flujo-caja` con middleware `auth` |
| `resources/views/layouts/menu.blade.php` | Ítem "Flujo de Caja" con ícono `fa-cash-register` entre Servicio Técnico y Administración |

---

## Base de datos

### Tabla `cajas_diarias`

Una fila por concesión por día. Índice único en `(id_concession, fecha)`.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint PK | |
| `id_concession` | bigint FK | FK → concessions |
| `fecha` | date | Día de la caja |
| `estado` | enum | `abierta` \| `cerrada` |
| `apertura_caja` | decimal(12,2) | Cargado desde cierre del día hábil anterior, editable |
| `deposito_dia` | decimal(12,2) | Suma de movimientos `deposito_banco` del día |
| `cierre_caja` | decimal(12,2) | Persistido al cerrar el día |
| `apertura_tecnoelectro` | decimal(12,2) | Cargado desde cierre Tecnoelectro del día hábil anterior, editable |
| `deposito_tecnoelectro` | decimal(12,2) | Suma de depósitos Tecnoelectro |
| `cierre_tecnoelectro` | decimal(12,2) | Persistido al cerrar el día |

### Tabla `movimientos_caja`

Una fila por cada transacción registrada. **Distinta a la tabla `logs` del sistema.**

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | bigint PK | |
| `caja_id` | bigint FK | FK → cajas_diarias |
| `id_concession` | bigint FK | FK → concessions (para queries directas) |
| `fecha` | date | Día asociado |
| `tipo_movimiento` | enum | `ingreso` \| `egreso` |
| `medio` | enum | `efectivo` \| `credito_debito` \| `transferencia` \| `tecnoelectro` \| `deposito_banco` |
| `monto` | decimal(12,2) | |
| `detalle` | string(255) | Descripción opcional |
| `anulado` | boolean | `false` por defecto; `true` al anular (no se elimina físicamente) |
| `usuario_id` | bigint FK | FK → users |

---

## Fórmulas de cierre

```
Cierre caja chica =
    apertura_caja
  + SUM(ingresos con medio = 'efectivo')
  - SUM(egresos con medio = 'efectivo')
  - deposito_dia  [= SUM(movimientos con medio = 'deposito_banco')]

Cierre Tecnoelectro =
    apertura_tecnoelectro
  + SUM(ingresos con medio = 'tecnoelectro')
  - SUM(egresos con medio = 'tecnoelectro')
  - deposito_tecnoelectro
```

Los cierres se **calculan en tiempo real** mientras la caja está abierta y se **persisten** al ejecutar el cierre del día.

---

## Rutas registradas

```
GET    /flujo-caja                              flujo_caja.index
GET    /flujo-caja/dia                          flujo_caja.dia       (AJAX cambio de fecha)
POST   /flujo-caja/movimiento                   flujo_caja.movimiento
POST   /flujo-caja/movimiento/{id}/anular       flujo_caja.anular
PATCH  /flujo-caja/{caja}/apertura              flujo_caja.apertura
POST   /flujo-caja/{caja}/cerrar                flujo_caja.cerrar
POST   /flujo-caja/{caja}/reabrir               flujo_caja.reabrir
```

Todas bajo middleware `auth`.

---

## Controller: métodos principales

| Método | Descripción |
|---|---|
| `index()` | Carga (o crea) la caja del día solicitado. Si no existe, la crea con aperturas desde el día hábil anterior |
| `cargarDia()` | AJAX: retorna datos JSON al cambiar el selector de fecha |
| `registrarMovimiento()` | Crea un `MovimientoCaja` y recalcula depósitos |
| `anularMovimiento()` | Marca `anulado = true` en un movimiento; recalcula depósitos |
| `actualizarAperturas()` | Actualiza `apertura_caja` y/o `apertura_tecnoelectro` |
| `cerrarCaja()` | Persiste los cierres calculados y cambia estado a `cerrada` |
| `reabrirCaja()` | Cambia estado a `abierta` |

Todos los métodos registran en la tabla `logs` del sistema (auditoría global).

---

## Lógica de día hábil anterior

Implementada en `CajaDiaria::diaHabilAnterior()`:

- Si hoy es **lunes** → busca el cierre del **sábado anterior**
- Cualquier otro día → busca el cierre del **día anterior**
- Si no existe registro anterior → apertura en `0` (editable manualmente)

---

## Vista principal

La vista `flujo_caja/index.blade.php` incluye:

- **Selector de fecha** (máximo: hoy) — recarga la página al cambiar
- **Badge de estado** de la caja (abierta / cerrada) con fecha en texto
- **Cards de apertura** editables para caja chica y Tecnoelectro con botón guardar (AJAX)
- **Formulario de registro** de movimiento (solo visible si la caja está abierta), con campos: tipo, medio, monto, detalle
- **Tabla de movimientos** del día con botón anular por fila (solo si la caja está abierta)
- **Resumen por medio** en tabla (efectivo, crédito/débito, transferencia, Tecnoelectro, depósito banco)
- **Cards de cierre** con desglose de la fórmula para caja chica y Tecnoelectro
- **Botón Cerrar / Reabrir** caja según estado actual

Toda la interactividad (registrar, anular, guardar apertura, cerrar, reabrir) usa AJAX sin recarga de página. Solo cerrar y reabrir recargan la página (para actualizar el estado de los controles).

Las notificaciones usan una función `notify()` local basada en alertas Bootstrap flotantes (sin dependencia de toastr ni librerías externas).

---

## Notas técnicas

- La tabla `movimientos_caja` es independiente de la tabla `logs`. Los `logs` se siguen escribiendo para el historial global del sistema.
- Los depósitos al banco no son un campo separado sino movimientos con `medio = 'deposito_banco'`, lo que permite múltiples depósitos diarios con trazabilidad individual.
- El módulo respeta la convención de multi-tenancy del proyecto: todas las queries filtran por `id_concession = auth()->user()->id_concession`.
- No se usó repositorio — se siguió el patrón de Eloquent directo en controller establecido por los módulos más recientes del proyecto (Cliente, OrdenServicio, etc.).
