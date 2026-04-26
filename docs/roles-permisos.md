# Sistema de Roles y Permisos

Implementado con `spatie/laravel-permission ^5.11` sobre Laravel 8.

---

## Roles

| Rol | Descripción |
|---|---|
| `super_admin` | Acceso total. Gestiona concesiones y representantes. |
| `administrador` | Administra su concesión: usuarios, inventario, servicio técnico. |
| `operador_servicio` | Operación diaria: órdenes, clientes, productos, flujo de caja (sin reabrir). |

---

## Permisos por módulo

### Inventario
| Permiso | super_admin | administrador | operador_servicio |
|---|:---:|:---:|:---:|
| `productos.ver` | ✅ | ✅ | ✅ |
| `productos.crear` | ✅ | ✅ | ✅ |
| `productos.editar` | ✅ | ✅ | ✅ |
| `productos.eliminar` | ✅ | ✅ | ✅ |
| `productos.importar` | ✅ | ✅ | ❌ |
| `bodegas.ver/crear/editar/eliminar` | ✅ | ✅ | ❌ |
| `categorias.ver/crear/editar/eliminar` | ✅ | ✅ | ❌ |

### Servicio Técnico
| Permiso | super_admin | administrador | operador_servicio |
|---|:---:|:---:|:---:|
| `ordenes.ver/crear/editar/cerrar` | ✅ | ✅ | ✅ |
| `ordenes.eliminar` | ✅ | ✅ | ❌ |
| `clientes.ver/crear/editar` | ✅ | ✅ | ✅ |
| `clientes.eliminar` | ✅ | ✅ | ❌ |
| `tecnicos.ver/crear/editar/eliminar` | ✅ | ✅ | ❌ |
| `servicios.ver` | ✅ | ✅ | ✅ |
| `servicios.crear/editar/eliminar` | ✅ | ✅ | ❌ |
| `artefactos.ver` | ✅ | ✅ | ✅ |
| `artefactos.crear/editar/eliminar/importar` | ✅ | ✅ | ❌ |
| `tipo_artefactos.*` | ✅ | ✅ | ❌ |

### Flujo de Caja
| Permiso | super_admin | administrador | operador_servicio |
|---|:---:|:---:|:---:|
| `flujo_caja.ver` | ✅ | ✅ | ✅ |
| `flujo_caja.operar` | ✅ | ✅ | ✅ |
| `flujo_caja.reabrir` | ✅ | ✅ | ❌ |

### Administración
| Permiso | super_admin | administrador | operador_servicio |
|---|:---:|:---:|:---:|
| `historial.ver` | ✅ | ✅ | ❌ |
| `usuarios.ver/crear/editar/eliminar/asignar_rol` | ✅ | ✅ | ❌ |
| `concesiones.*` | ✅ | ❌ | ❌ |
| `representantes.*` | ✅ | ❌ | ❌ |

---

## Arquitectura — 3 niveles de protección

### 1. Rutas (`routes/web.php`)
Cada grupo de rutas usa `middleware(['auth', 'permission:modulo.accion'])`:
```php
Route::middleware(['auth', 'permission:ordenes.ver'])->group(function () {
    Route::resource('ordenes_servicio', OrdenServicioController::class);
});
```

### 2. Menú Blade (`resources/views/layouts/menu.blade.php`)
Cada sección del menú está envuelta en `@can` / `@canany`:
```blade
@can('bodegas.ver')
    <li>...</li>
@endcan
```

### 3. Controladores
Validación extra en acciones críticas. Ejemplo en `FlujoCajaController::reabrirCaja()`:
```php
if (!auth()->user()->can('flujo_caja.reabrir')) {
    abort(403, 'No tienes permiso para reabrir una caja cerrada.');
}
```

---

## Gestión de usuarios y roles

Los administradores pueden asignar roles desde `/users`. El campo "Rol" en el formulario de usuario permite seleccionar entre los roles disponibles según quién edita:
- `super_admin` puede asignar cualquier rol (incluyendo `super_admin`)
- `administrador` solo puede asignar `administrador` u `operador_servicio`

---

## Seeder

Para recrear roles y permisos desde cero:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

El seeder es idempotente (`firstOrCreate` + `syncPermissions`), se puede ejecutar múltiples veces sin duplicados.

---

## Asignar super_admin manualmente (via Tinker)
```bash
php artisan tinker
>>> $user = App\Models\User::where('email', 'tu@email.com')->first();
>>> $user->syncRoles(['super_admin']);
```

---

## Guía práctica: mostrar y ocultar funcionalidades en vistas

### Ocultar un botón a un usuario

Envuelve el botón con `@can` usando el permiso correspondiente:

```blade
@can('ordenes.eliminar')
    <button class="btn btn-danger">Eliminar</button>
@endcan
```

El operador de servicio no tiene `ordenes.eliminar`, por lo que el botón no aparecerá para él.

---

### Mostrar algo solo a un rol específico

Usa `@role` cuando la lógica es por rol y no por permiso puntual:

```blade
@role('super_admin')
    <a href="{{ route('concessions.index') }}">Gestionar Concesiones</a>
@endrole
```

Para múltiples roles usa `@hasanyrole`:

```blade
@hasanyrole(['super_admin', 'administrador'])
    <a href="{{ route('users.index') }}">Usuarios</a>
@endhasanyrole
```

---

### Ocultar una sección completa de la vista

```blade
@canany(['bodegas.ver', 'categorias.ver'])
    <div class="card">
        {{-- Contenido visible solo si tiene al menos uno de esos permisos --}}
    </div>
@endcanany
```

---

### Mostrar algo solo a quien NO tiene un permiso

```blade
@cannot('flujo_caja.reabrir')
    <p class="text-muted">Solo un administrador puede reabrir una caja cerrada.</p>
@endcannot
```

---

### Deshabilitar un campo de formulario (visible pero bloqueado)

Útil cuando quieres que el operador vea el dato pero no lo edite:

```blade
<input type="text" name="campo"
    value="{{ $valor }}"
    class="form-control"
    @cannot('ordenes.editar') disabled @endcannot>
```

---

### Validar en el controlador (backend — nunca omitir)

La UI puede ocultarse, pero siempre valida en el servidor también. Dos formas:

**Opción A — `abort` directo:**
```php
public function destroy($id)
{
    if (!auth()->user()->can('ordenes.eliminar')) {
        abort(403);
    }
    // ...
}
```

**Opción B — `authorize` de Laravel (lanza 403 automáticamente):**
```php
public function destroy($id)
{
    $this->authorize('ordenes.eliminar');
    // ...
}
```

---

### Referencia rápida de directivas Blade

| Directiva | Uso |
|---|---|
| `@can('permiso')` / `@endcan` | Muestra si el usuario tiene ese permiso |
| `@cannot('permiso')` / `@endcannot` | Muestra si el usuario NO tiene ese permiso |
| `@canany(['p1','p2'])` / `@endcanany` | Muestra si tiene al menos uno de los permisos |
| `@role('nombre')` / `@endrole` | Muestra si el usuario tiene ese rol exacto |
| `@hasanyrole(['r1','r2'])` / `@endhasanyrole` | Muestra si tiene al menos uno de los roles |

> **Regla de oro:** `@can` controla visibilidad en la vista, pero la seguridad real siempre debe estar también en el controlador. Nunca confíes solo en ocultar elementos HTML.

---

## Cómo crear nuevos permisos

### Paso 1 — Agregar el permiso al seeder

Abre [database/seeders/RolesAndPermissionsSeeder.php](database/seeders/RolesAndPermissionsSeeder.php) y agrega el nuevo permiso al array `$permisos`:

```php
$permisos = [
    // ...permisos existentes...

    // Nuevo módulo o acción
    'reportes.ver',
    'reportes.exportar',
];
```

Luego agrégalo al rol correspondiente en `syncPermissions`:

```php
$administrador->syncPermissions([
    // ...permisos existentes...
    'reportes.ver',
    'reportes.exportar',
]);

$operador->syncPermissions([
    // ...permisos existentes...
    'reportes.ver', // solo ver, no exportar
]);
```

### Paso 2 — Ejecutar el seeder

El seeder es **idempotente**: no borra lo que ya existe, solo agrega lo nuevo.

```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Paso 3 — Limpiar caché de permisos

```bash
php artisan cache:clear
```

### Paso 4 — Usar el permiso en la aplicación

**En rutas** (`routes/web.php`):
```php
Route::middleware(['auth', 'permission:reportes.ver'])->group(function () {
    Route::get('reportes', [ReporteController::class, 'index'])->name('reportes.index');
});
```

**En vistas** (`*.blade.php`):
```blade
@can('reportes.exportar')
    <button class="btn btn-brand">Exportar Excel</button>
@endcan
```

**En controladores**:
```php
public function exportar()
{
    $this->authorize('reportes.exportar');
    // ...
}
```

### Alternativa: crear el permiso desde Tinker (sin tocar el seeder)

Útil para pruebas rápidas o permisos puntuales:

```bash
php artisan tinker

>>> $p = Spatie\Permission\Models\Permission::create(['name' => 'reportes.ver']);
>>> $rol = Spatie\Permission\Models\Role::findByName('administrador');
>>> $rol->givePermissionTo($p);
```

> **Importante:** Si creas permisos por Tinker, recuerda agregarlos también al seeder para que persistan en producción y en otros ambientes.

---

## Tablas de base de datos relacionadas

Spatie usa 5 tablas propias. Ninguna debe modificarse manualmente — todo se gestiona via seeder, Tinker o la interfaz de `/roles`.

| Tabla | Descripción | Columnas clave |
|---|---|---|
| `permissions` | Catálogo de todos los permisos | `id`, `name`, `guard_name` |
| `roles` | Catálogo de roles | `id`, `name`, `guard_name` |
| `role_has_permissions` | Qué permisos tiene cada rol | `permission_id`, `role_id` |
| `model_has_roles` | Qué rol tiene cada usuario | `role_id`, `model_id` (= `users.id`), `model_type` |
| `model_has_permissions` | Permisos directos a un usuario (sin pasar por rol) | `permission_id`, `model_id`, `model_type` |

### Relación entre tablas

```
users
  └── model_has_roles         → qué rol tiene el usuario
        └── roles
              └── role_has_permissions  → qué permisos tiene ese rol
                    └── permissions
```

### Consultas útiles en Tinker para diagnóstico

```bash
php artisan tinker

# Ver todos los permisos de un usuario
>>> App\Models\User::find(1)->getAllPermissions()->pluck('name')

# Ver el rol de un usuario
>>> App\Models\User::find(1)->getRoleNames()

# Ver todos los permisos de un rol
>>> Spatie\Permission\Models\Role::findByName('operador_servicio')->permissions->pluck('name')

# Ver todos los roles del sistema
>>> Spatie\Permission\Models\Role::all()->pluck('name')

# Ver todos los permisos del sistema
>>> Spatie\Permission\Models\Permission::all()->pluck('name')
```

---

## Archivos modificados

| Archivo | Cambio |
|---|---|
| `composer.json` | Dependencia `spatie/laravel-permission ^5.11` |
| `app/Models/User.php` | Trait `HasRoles` |
| `app/Http/Kernel.php` | Middleware `role`, `permission`, `role_or_permission` |
| `routes/web.php` | Grupos con `middleware(['auth', 'permission:...'])` |
| `resources/views/layouts/menu.blade.php` | Directivas `@can` / `@canany` |
| `app/Http/Controllers/FlujoCajaController.php` | Check `flujo_caja.reabrir` en `reabrirCaja()` |
| `app/Http/Controllers/UserController.php` | Pasar roles a vistas, guardar rol al crear/editar |
| `resources/views/users/fields.blade.php` | Selector de rol |
| `resources/views/users/table.blade.php` | Columna de rol con badge |
| `resources/views/users/create.blade.php` | Layout actualizado |
| `resources/views/users/edit.blade.php` | Layout actualizado |
| `database/migrations/2026_04_26_*_create_permission_tables.php` | Tablas de spatie |
| `database/seeders/RolesAndPermissionsSeeder.php` | Roles, permisos y asignación inicial |
