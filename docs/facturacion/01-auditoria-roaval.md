# Auditoría Técnica de ROAVAL

Fecha: 2026-10-27
Proyecto: ROAVAL — Sistema de Inventario para Concesionarias
Ruta: `/Applications/MAMP/htdocs/inventario_concesion/`

---

## 1. Stack Tecnológico

| Componente | Versión | Fuente |
|---|---|---|
| PHP | 8.2.26 | `composer.json` → `config.platform.php` |
| Laravel | 8.83.27 | `composer.lock` (última versión de Laravel 8, EOL enero 2024) |
| MySQL | MAMP default | `.env.example` → `DB_CONNECTION=mysql`, puerto 3306 |
| Servidor web | Apache (MAMP) | Infraestructura local macOS |
| Frontend | Blade + AdminLTE + Bootstrap 5.1.3 + jQuery | `webpack.mix.js`, `package.json` |
| Build tool | Laravel Mix (Webpack) | `webpack.mix.js` |

### Dependencias PHP principales (`composer.json`)

| Paquete | Versión constraint | Uso |
|---|---|---|
| `laravel/framework` | ^8.75 | Framework principal |
| `spatie/laravel-permission` | ^5.11 | Roles y permisos |
| `barryvdh/laravel-dompdf` | ^2.2 | Generación de PDF |
| `maatwebsite/excel` | ^3.1 | Import/Export Excel |
| `guzzlehttp/guzzle` | ^7.0.1 | HTTP client |
| `laravel/sanctum` | ^2.11 | API tokens |
| `intervention/image` | ^3.0 | Procesamiento de imágenes |
| `doctrine/dbal` | ^3.6 | Migraciones avanzadas |
| `infyomlabs/adminlte-templates` | ^3.0 | Templates AdminLTE |
| `laracasts/flash` | ^3.2 | Mensajes flash |
| `laravelcollective/html` | ^6.4 | Form/HTML helpers |

### Dependencias NPM (`package.json`)

- `@popperjs/core`, `axios`, `bootstrap 5.1.3`, `lodash`, `postcss`, `sass`

---

## 2. Estructura de Base de Datos

Total de migraciones: **45 archivos** en `database/migrations/`.

### 2.1 Entidades Principales

#### `concessions` (la entidad "empresa" / tenant)
```
id, name, address, id_representative (FK → representative),
numero_orden_siguiente, timestamps, soft_deletes
```
- Migración: `2023_04_07_215350_create_concessions_table.php`
- **CARECE de datos fiscales**: sin RUT empresa, giro, actividad económica, comuna, ciudad
- Solo tiene `name` y `address`

#### `users`
```
id, name, email (unique), email_verified_at, password,
id_concession (nullable FK → concessions), timestamps
```
- Migración: `2023_04_08_000000_create_users_table.php`
- Relación directa `id_concession` + pivot `user_concession` (many-to-many)

#### `user_concession` (pivot users ↔ concessions)
```
id, id_user (FK), id_concession (FK), timestamps
```
- Migración: `2023_04_18_213208_create_user_concession_table.php`

#### `representative` (representantes legales)
```
id, name, rut, phone, city, address, email, id_concession, timestamps
```
- Tiene `rut` del representante legal, no de la empresa

### 2.2 Órdenes de Servicio (transacción principal)

#### `ordenes_servicio`
```
id, numero (unique), folio_garantia, tipo_servicio (enum: mantenimiento/
reparacion/instalacion/garantia), fecha_orden, fecha_visita, cliente_id (FK),
artefacto_id (FK), descripcion_falla, observaciones, tipo_atencion (enum:
taller/terreno), valor_visita (decimal 10,2), costo_total (decimal 10,2),
tecnico_id (FK), estado (enum: pendiente/en_progreso/finalizada/cancelada),
id_concession, tipo_asistencia, timestamps, soft_deletes
```
- Migración: `2024_01_01_000004_create_ordenes_servicio_table.php`

#### `orden_servicio_detalles`
```
id, orden_servicio_id (FK cascade), producto_id (FK nullable),
servicio_id (FK nullable), cantidad, precio_unitario (decimal 10,2),
subtotal (decimal 10,2), nota, timestamps
```
- Migración: `2024_01_01_000005_create_orden_servicio_detalles_table.php`

### 2.3 Clientes

#### `clientes`
```
id, nombre, apellido, direccion, ciudad, coordenadas, numero_contacto,
nota, email, tipo_cliente (enum: residencial/empresa/concesion), rut,
estado (boolean), id_concession, timestamps
```
- **Tiene `rut`** — dato necesario como RUTRecep en boletas
- `tipo_cliente` permite distinguir entre consumidor final y empresa

### 2.4 Productos, Servicios e Inventario

#### `products`
```
id, name, description, code, id_concession (FK), id_category (FK),
timestamps, soft_deletes
```

#### `servicios`
```
id, nombre_servicio, precio (decimal 10,2), descripcion,
duracion_estimada, estado (boolean), requiere_repuestos (boolean),
id_concession, timestamps
```

#### `stores` (bodegas)
```
id, name, address, id_concession (FK), timestamps, soft_deletes
```

#### `product_stores` (pivot producto × bodega con stock)
```
id, id_product (FK), id_store (FK), id_responsible (FK → users),
stock (default 0), timestamps
```

### 2.5 Flujo de Caja

#### `cajas_diarias`
```
id, id_concession (FK cascade), fecha, estado (enum: abierta/cerrada),
apertura_caja (decimal 12,2), deposito_dia (decimal 12,2),
cierre_caja (decimal 12,2), apertura_tecnoelectro, deposito_tecnoelectro,
cierre_tecnoelectro, timestamps
```
- Unique constraint: `[id_concession, fecha]`

#### `movimientos_caja`
```
id, caja_id (FK cascade), id_concession (FK cascade), fecha,
tipo_movimiento (enum: ingreso/egreso), medio (enum: efectivo,
credito_debito, transferencia, transbank, ...), monto (decimal 12,2),
detalle, anulado (boolean), usuario_id (FK cascade), timestamps
```

### 2.6 Otras Tablas

- `artefactos` — equipos/artefactos en servicio técnico
- `tipo_artefactos` — categorías de artefactos
- `tecnicos` — técnicos con user_id, especialidad, disponibilidad
- `ofertas` — ofertas para landing pública (con JSON de fotos)
- `logs` — log de actividad
- `logs_flujo_caja` — auditoría de flujo de caja
- `landing_visits`, `contactos_landing` — analytics de landing page
- Tablas Spatie: `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`

---

## 3. Modelos y Relaciones

Total: **24 modelos** en `app/Models/`.

### Mapa de relaciones principal

```
Concession
  ├── belongsTo User (representative)
  ├── belongsToMany User (via user_concession)
  └── [implícito] hasMany de casi todo via id_concession

User
  ├── belongsToMany Concession (via user_concession)
  ├── hasOne Tecnico
  └── uses HasRoles (Spatie)

OrdenServicio
  ├── belongsTo Cliente
  ├── belongsTo Artefacto
  ├── belongsTo Tecnico
  └── hasMany OrdenServicioDetalle

OrdenServicioDetalle
  ├── belongsTo OrdenServicio
  ├── belongsTo Product (producto_id)
  └── belongsTo Servicio

Cliente → hasMany OrdenServicio
Product → belongsTo Category_product, belongsToMany Store (via product_stores)
Artefacto → belongsTo TipoArtefacto, hasMany OrdenServicio
Tecnico → belongsTo User, hasMany OrdenServicio
CajaDiaria → hasMany MovimientoCaja, belongsTo Concession
MovimientoCaja → belongsTo CajaDiaria, belongsTo User, belongsTo Concession
```

---

## 4. Autenticación y Autorización

### Autenticación
- Session-based (Laravel `web` guard) via `laravel/ui` (`Auth::routes()`)
- Eloquent user provider
- Sanctum instalado pero solo usado en ruta default `api/user`
- Sin OAuth, social login, ni 2FA

### Autorización
- **`spatie/laravel-permission` v5.11.1** con teams **DESHABILITADO** (`config/permission.php` → `'teams' => false`)
- 3 roles: `super_admin`, `administrador`, `operador_servicio`
- **52 permisos** definidos en `RolesAndPermissionsSeeder`
- Convención: `recurso.accion` (ej: `productos.ver`, `ordenes.crear`)
- Sin Policies ni Gates — autorización exclusivamente via middleware Spatie y `auth()->user()->can()`

### Permisos por rol

| Recurso | super_admin | administrador | operador_servicio |
|---|---|---|---|
| productos | CRUD completo | CRUD completo | ver, crear, editar |
| bodegas | CRUD completo | CRUD completo | ver |
| ordenes | CRUD completo | CRUD completo | ver, crear, editar |
| clientes | CRUD completo | CRUD completo | ver, crear, editar |
| flujo_caja | Todo + informes | Todo + informes | ver, crear |
| concesiones | CRUD completo | — | — |
| representantes | CRUD completo | — | — |
| usuarios | CRUD completo | CRUD completo | — |

---

## 5. Infraestructura de Colas y Jobs

**NO EXISTE infraestructura de colas.**

- `QUEUE_CONNECTION=sync` en `.env.example`
- No existe directorio `app/Jobs/`
- Console Kernel (`app/Console/Kernel.php`) tiene schedule vacío
- No hay comandos Artisan registrados
- Todo el procesamiento es **síncrono**

Esto debe configurarse antes de integrar facturación electrónica (envío asíncrono al SII).

---

## 6. Almacenamiento de Archivos

- Disk default: `local` (`storage/app`)
- Configuración estándar en `config/filesystems.php`: `local`, `public`, `s3`
- S3 configurado pero sin credenciales en `.env.example`
- Ofertas almacenan fotos como JSON array de paths
- Import de artefactos usa `maatwebsite/excel`

---

## 7. Frontend

- **Motor de vistas**: Blade
- **Framework CSS**: Bootstrap 5.1.3 + AdminLTE (via `infyomlabs/adminlte-templates`)
- **JavaScript**: jQuery + vanilla JS
- **Tablas**: DataTables con server-side pagination
- **Gráficos**: Chart.js en informes de flujo de caja
- **Build**: Laravel Mix (Webpack) via `webpack.mix.js`
- **Sin frameworks SPA**: No Vue, React, Livewire

### Estructura de vistas (26 directorios)
`artefactos/`, `auth/`, `category_products/`, `clientes/`, `common/`, `components/landing/`, `concessions/`, `contactos_landing/`, `flujo_caja/`, `landing/`, `layouts/`, `logs/`, `ofertas/`, `ordenes_servicio/`, `products/`, `representative/`, `roait/`, `roles/`, `servicios/`, `stores/`, `tecnicos/`, `tipo_artefactos/`, `users/`

---

## 8. Patrón Multi-tenancy

El proyecto usa `id_concession` como clave de tenant. Fue incorporado gradualmente:

1. **Diseño original (2023)**: tablas `concessions`, `users`, `products`, `stores` con `id_concession`
2. **Expansión posterior (2026)**: migraciones `2026_04_15` a `2026_04_16` agregaron `id_concession` a: `ordenes_servicio`, `clientes`, `artefactos`, `servicios`, `tecnicos`, `representative`
3. **Seeder de backfill**: `2026_04_16_000006_seed_id_concession_existing_records.php`

**Implementación en controllers**: filtrado explícito con `where('id_concession', auth()->user()->id_concession)`. No usa global scopes.

---

## 9. Funcionalidad de Facturación Existente

**NO EXISTE ninguna funcionalidad de facturación o DTE.**

Búsqueda de términos relacionados (`factura`, `boleta`, `dte`, `sii`, `tributario`, `impuesto`, `iva`, `tax`, `billing`, `invoice`, `fiscal`) devolvió **cero resultados** en el codebase.

### Lo que SÍ existe y es relevante

1. **PDF de órdenes de servicio**: `OrdenServicioController::pdf()` usa `barryvdh/laravel-dompdf` para generar PDF profesional con datos del cliente, detalles del servicio, líneas con precios y total. Template en `resources/views/ordenes_servicio/pdf.blade.php`. **Sin cálculos de IVA ni referencias DTE.**

2. **Tracking de costos**: `OrdenServicio` tiene `costo_total` y `valor_visita` (decimal 10,2). `OrdenServicioDetalle` tiene `cantidad`, `precio_unitario`, `subtotal`. Son **valores netos sin desglose tributario**.

3. **Datos de cliente con RUT**: tabla `clientes` tiene campo `rut` — necesario como RUTRecep.

4. **Datos de concesión**: solo `name` y `address`. **Faltan**: RUT empresa, razón social, giro, actividad económica, comuna, ciudad, datos de resolución SII.

---

## 10. Conclusiones para la Integración de Facturación

### Se puede construir sobre

- Patrón multi-tenancy `id_concession` bien establecido
- Tabla `clientes` con RUT
- Sistema de permisos Spatie extensible
- DomPDF para generación de PDFs
- Guzzle HTTP client para comunicación con SII
- Patrón `lockForUpdate()` en `OrdenServicioController` (línea 143) — aplicable a asignación atómica de folios
- `Crypt::encryptString()` de Laravel para cifrado de contraseñas de certificados

### Se debe construir desde cero

- Tablas de facturación: documentos tributarios, folios, certificados, configuración fiscal
- Cálculos tributarios (IVA)
- Construcción XML DTE
- Firma digital XMLDSIG
- Timbre electrónico (TED)
- Comunicación con SII (autenticación, envío, consultas)
- Infraestructura de colas (Jobs, queue driver `database`)
- UI de facturación (vistas Blade)
- Datos fiscales en concesiones (tabla nueva o extensión)
