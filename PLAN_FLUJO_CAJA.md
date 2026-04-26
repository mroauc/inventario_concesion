# Plan de Implementación: Módulo Flujo de Caja

**Fecha:** 2026-04-23  
**Sistema:** Laravel 8 — Inventario Concesión  
**Autor del plan:** Claude Code  

---

## 1. Contexto y Decisiones de Diseño

### Decisiones clave adoptadas

| Pregunta | Decisión |
|---|---|
| ¿Persistir o calcular cierres? | **Persistir**: el cierre se guarda al cerrar el día. No se recalcula retroactivamente. |
| ¿Editar/eliminar movimientos? | **Anulación** (soft delete + campo `anulado`), no eliminación física. Edición permitida solo si el día está abierto. |
| ¿Multiusuario o global? | **Por concesión**: todos los usuarios de una concesión operan la misma caja. Cada movimiento registra `usuario_id`. |
| ¿Cierre explícito o automático? | **Botón de cierre** con posibilidad de reabrir. Un campo `estado` en `cajas_diarias` controla esto. |
| ¿Depósito del día único o múltiple? | **Múltiple**: los depósitos son movimientos con medio `deposito_banco`. |
| ¿Apertura Tecnoelectro? | Se carga automáticamente desde el cierre Tecnoelectro del día hábil anterior; es editable. |
| ¿Vinculado a órdenes de servicio? | **No**: los movimientos son registros independientes. |

### ¿Qué es Tecnoelectro en este módulo?
Tecnoelectro es el software de emisión de boletas/facturas electrónicas (conectado al SII). En la caja, representa el dinero cobrado a través de ese sistema (documentos tributarios). Tiene su propio subflujo: apertura, movimientos, depósito y cierre propios, paralelo al flujo de caja chica general.

---

## 2. Modelo de Base de Datos

### Tabla: `cajas_diarias`

Registra el estado de la caja por día y por concesión.

```
id                      bigint PK
id_concession           bigint FK → concessions
fecha                   date                        -- día de la caja
estado                  enum('abierta','cerrada')    -- default 'abierta'
apertura_caja           decimal(12,2)  default 0    -- carga desde cierre día anterior, editable
deposito_dia            decimal(12,2)  default 0    -- suma de todos los depósitos banco del día
cierre_caja             decimal(12,2)  default 0    -- calculado y persistido al cerrar
apertura_tecnoelectro   decimal(12,2)  default 0    -- carga desde cierre Tecnoelectro día anterior, editable
deposito_tecnoelectro   decimal(12,2)  default 0    -- suma de depósitos Tecnoelectro del día
cierre_tecnoelectro     decimal(12,2)  default 0    -- calculado y persistido al cerrar
created_at / updated_at timestamps
```

**Índice único**: `(id_concession, fecha)` — solo puede haber una caja por concesión por día.

---

### Tabla: `movimientos_caja`

Registra cada movimiento de dinero del día. Es distinta a la tabla `logs` existente.

```
id                  bigint PK
caja_id             bigint FK → cajas_diarias
id_concession       bigint FK → concessions      -- para queries directas sin join
fecha               date                          -- día asociado (= cajas_diarias.fecha)
tipo_movimiento     enum('ingreso','egreso')
medio               enum('efectivo','credito_debito','transferencia','tecnoelectro','deposito_banco')
monto               decimal(12,2)
detalle             string(255) nullable
anulado             boolean default false
usuario_id          bigint FK → users
created_at / updated_at timestamps
```

**Nota sobre `deposito_banco`**: los depósitos al banco son un tipo de movimiento egreso con medio `deposito_banco`. Esto permite múltiples depósitos en el día y que queden trazados individualmente.

---

### Fórmulas de cierre

```
// Caja chica
cierre_caja = apertura_caja
            + SUM(ingresos efectivo)
            - SUM(egresos efectivo, excluir deposito_banco)
            - deposito_dia   (= SUM movimientos tipo deposito_banco)

// Tecnoelectro
cierre_tecnoelectro = apertura_tecnoelectro
                    + SUM(ingresos tecnoelectro)
                    - SUM(egresos tecnoelectro)
                    - deposito_tecnoelectro

// deposito_dia y deposito_tecnoelectro se calculan dinámicamente desde movimientos_caja
```

---

## 3. Archivos a Crear / Modificar

### Archivos nuevos

| Archivo | Descripción |
|---|---|
| `database/migrations/2026_04_23_000001_create_cajas_diarias_table.php` | Migración tabla cajas_diarias |
| `database/migrations/2026_04_23_000002_create_movimientos_caja_table.php` | Migración tabla movimientos_caja |
| `app/Models/CajaDiaria.php` | Modelo Eloquent |
| `app/Models/MovimientoCaja.php` | Modelo Eloquent |
| `app/Http/Controllers/FlujoCajaController.php` | Controller principal (no repositorio, Eloquent directo) |
| `resources/views/flujo_caja/index.blade.php` | Vista principal del módulo |
| `resources/views/flujo_caja/partials/_movimientos_table.blade.php` | Tabla de movimientos (partial para AJAX) |

### Archivos a modificar

| Archivo | Cambio |
|---|---|
| `routes/web.php` | Agregar rutas del módulo flujo de caja |
| `resources/views/layouts/menu.blade.php` | Agregar enlace "Flujo de Caja" en sección Servicio Técnico o nueva sección |

---

## 4. Rutas a Definir

```php
// Flujo de caja
Route::get('/flujo-caja', [FlujoCajaController::class, 'index'])->name('flujo_caja.index');
Route::post('/flujo-caja/abrir', [FlujoCajaController::class, 'abrirCaja'])->name('flujo_caja.abrir');
Route::post('/flujo-caja/cerrar/{caja}', [FlujoCajaController::class, 'cerrarCaja'])->name('flujo_caja.cerrar');
Route::post('/flujo-caja/reabrir/{caja}', [FlujoCajaController::class, 'reabrirCaja'])->name('flujo_caja.reabrir');
Route::post('/flujo-caja/movimiento', [FlujoCajaController::class, 'registrarMovimiento'])->name('flujo_caja.movimiento');
Route::post('/flujo-caja/movimiento/{movimiento}/anular', [FlujoCajaController::class, 'anularMovimiento'])->name('flujo_caja.anular');
Route::patch('/flujo-caja/{caja}/apertura', [FlujoCajaController::class, 'actualizarAperturas'])->name('flujo_caja.apertura');
Route::get('/flujo-caja/dia', [FlujoCajaController::class, 'cargarDia'])->name('flujo_caja.dia');  // AJAX: cambiar fecha
```

---

## 5. Lógica de Negocio Relevante

### Día hábil anterior
- Lunes → cierre del sábado anterior
- Cualquier otro día → cierre del día anterior
- Si no existe cierre anterior → campos de apertura en 0 (editables manualmente)

### Control de estado de caja
- Al cargar el módulo, si no existe `cajas_diarias` para hoy → se crea automáticamente con estado `abierta` y aperturas cargadas desde el día anterior.
- Estado `cerrada` → solo lectura. Botón "Reabrir" disponible para cualquier usuario (según decisión B).
- Estado `abierta` → permite registrar movimientos, anular movimientos, editar aperturas.

### Auditoría
- Todo cambio (crear movimiento, anular, editar apertura, cerrar, reabrir) se registra en `movimientos_caja` con `usuario_id`.
- Adicionalmente, se escribe en la tabla `logs` existente para mantener consistencia con el historial global del sistema.

---

## 6. Estructura de la Vista Principal

```
┌─────────────────────────────────────────────────────────┐
│  Flujo de Caja                    [Selector de fecha]   │
├────────────────────────┬────────────────────────────────┤
│  CAJA CHICA            │  TECNOELECTRO                  │
│  Apertura: [_______]   │  Apertura: [_______]           │
│                        │                                │
│  [+ Registrar movim.]  │                                │
├────────────────────────┴────────────────────────────────┤
│  MOVIMIENTOS DEL DÍA                                    │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Fecha | Tipo | Medio | Detalle | Monto | Acciones│   │
│  └─────────────────────────────────────────────────┘   │
├─────────────────────────────────────────────────────────┤
│  RESUMEN DEL DÍA                                        │
│  Efectivo | Cred/Déb | Transferencia | Tecnoelectro     │
│  Total ingresos | Total egresos | Depósito | Cierre     │
├─────────────────────────────────────────────────────────┤
│  [CERRAR CAJA DEL DÍA]  /  [REABRIR CAJA]              │
└─────────────────────────────────────────────────────────┘
```

---

## 7. Riesgos Identificados

| Riesgo | Mitigación |
|---|---|
| Usuario edita apertura después de registrar movimientos | Permitir edición siempre que el día esté abierto; recalcular cierre en tiempo real en la vista |
| Concurrencia: dos usuarios registran movimientos al mismo tiempo | No es crítico ya que no hay stock ni secuencias que bloquear; timestamps resuelven e¬l orden |
| Días sin actividad (domingos) quedan sin registro | Solo crear `cajas_diarias` cuando el usuario accede o registra algo; no crear vacíos automáticos |
| Depósitos al banco confundidos con egresos normales | Medio `deposito_banco` diferenciado en enum; UI lo muestra claramente separado |
| Cierre Tecnoelectro del día anterior no existe | Si `cierre_tecnoelectro = 0` o no existe el registro, mostrar 0 y permitir edición manual |

---

## 8. Plan por Fases

### Fase 1 — Base de datos y modelos
- [ ] Crear migración `cajas_diarias`
- [ ] Crear migración `movimientos_caja`
- [ ] Crear modelo `CajaDiaria` con relaciones y métodos de cálculo
- [ ] Crear modelo `MovimientoCaja` con relaciones
- [ ] Ejecutar migraciones

### Fase 2 — Controller y rutas
- [ ] Crear `FlujoCajaController` con métodos: `index`, `abrirCaja`, `cerrarCaja`, `reabrirCaja`, `registrarMovimiento`, `anularMovimiento`, `actualizarAperturas`, `cargarDia`
- [ ] Registrar rutas en `routes/web.php`
- [ ] Lógica de carga del día hábil anterior

### Fase 3 — Vista principal
- [ ] Vista `flujo_caja/index.blade.php` con selector de fecha
- [ ] Card de apertura caja chica y Tecnoelectro
- [ ] Card de registro de movimiento (modal o inline)
- [ ] Tabla de movimientos del día con botón anular
- [ ] Cards de resumen/totales calculados en tiempo real (JS)
- [ ] Botón cerrar/reabrir caja

### Fase 4 — Integración y menú
- [ ] Agregar entrada al menú en `menu.blade.php`
- [ ] Agregar logging a tabla `logs` en todas las acciones del controller
- [ ] Pruebas funcionales: flujo completo de apertura → movimientos → cierre → siguiente día

---

## 9. Preguntas pendientes (resueltas)

| Pregunta | Respuesta |
|---|---|
| ¿Qué es Tecnoelectro? | Software de boletas/facturas electrónicas (SII) |
| ¿Multiusuario? | Todos equivalentes; caja es por concesión |
| ¿Cierre explícito? | Sí, botón de cierre con opción de reabrir |
| ¿Depósito único o múltiple? | Múltiple (son movimientos tipo `deposito_banco`) |
| ¿Apertura Tecnoelectro? | Auto desde cierre anterior, editable |
| ¿Vinculado a OT? | No, registros independientes |
| ¿Tabla separada de logs? | Sí: `movimientos_caja` es independiente de la tabla `logs` |

---

*Plan listo para implementar. Confirmar inicio de Fase 1 para proceder.*
