# Documentación del Menú de Navegación

## Archivo: `resources/views/layouts/menu.blade.php`

---

## Estructura del Menú

El menú lateral (sidebar) está organizado en cuatro secciones principales. Utiliza el componente **AdminLTE Treeview** para los submenús, activado mediante `data-widget="treeview"` en el `<ul>` padre del sidebar.

```
Inicio
├── Inventario
│   ├── Productos
│   ├── Bodegas
│   ├── Categorías
│   └── Importar Productos
├── Servicio Técnico
│   ├── Órdenes de Servicio
│   ├── Clientes
│   ├── Técnicos
│   └── Servicios
├── Administración
│   ├── Concesiones
│   ├── Representantes
│   └── Historial
└── Usuarios  ← solo visible para el administrador
```

---

## Secciones

### 1. Inicio
- Ruta: `home`
- Icono: `fas fa-home`
- Activo cuando: `Request::is('/') || Request::is('home*')`

### 2. Inventario
Agrupa todo lo relacionado con el manejo de stock, ubicaciones físicas y catálogo de productos.

| Opción            | Ruta                      | Patrón activo          |
|-------------------|---------------------------|------------------------|
| Productos         | `products.index`          | `products*`            |
| Bodegas           | `stores.index`            | `stores*`              |
| Categorías        | `categoryProducts.index`  | `categoryProducts*`    |
| Importar Productos| `products.index_importar` | `product/importar*`    |

### 3. Servicio Técnico
Agrupa el flujo de atención al cliente: órdenes de trabajo, clientes, técnicos y catálogo de servicios.

| Opción             | Ruta                      | Patrón activo           |
|--------------------|---------------------------|-------------------------|
| Órdenes de Servicio| `ordenes_servicio.index`  | `ordenes_servicio*`     |
| Clientes           | `clientes.index`          | `clientes*`             |
| Técnicos           | `tecnicos.index`          | `tecnicos*`             |
| Servicios          | `servicios.index`         | `servicios*`            |

### 4. Administración
Agrupa configuraciones generales del sistema.

| Opción        | Ruta                    | Patrón activo     |
|---------------|-------------------------|-------------------|
| Concesiones   | `concessions.index`     | `concessions*`    |
| Representantes| `representative.index`  | `representative*` |
| Historial     | `logs.index`            | `historial*`      |

### 5. Usuarios *(solo administrador)*
Visible únicamente para el usuario con email `marceloroa19@gmail.com`.

| Opción   | Ruta          | Patrón activo |
|----------|---------------|---------------|
| Usuarios | `users.index` | `users*`      |

---

## Comportamiento de la clase `active`

- **Ítems simples**: se agrega `active` al `<a class="nav-link">` usando `Request::is('patron*')`.
- **Submenús (treeview)**: el `<li>` padre recibe `menu-open` y su enlace recibe `active` cuando cualquiera de sus hijos está activo. Esto se controla con variables `@php $seccionActivo = ... @endphp` calculadas al inicio de cada bloque.

Ejemplo de lógica de apertura automática del submenú:

```blade
@php
    $inventarioActivo = Request::is('products*') || Request::is('stores*') || ...;
@endphp
<li class="nav-item has-treeview {{ $inventarioActivo ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $inventarioActivo ? 'active' : '' }}">
```

---

## Tecnologías utilizadas

- **AdminLTE 3**: proporciona el componente treeview del sidebar.
- **Font Awesome 5**: iconos con clases `fas`, `far`, `fa-*`.
- **Bootstrap 4**: estructura y estilos base.

---

## Convenciones de íconos

| Sección             | Ícono                |
|---------------------|----------------------|
| Inicio              | `fas fa-home`        |
| Inventario          | `fas fa-boxes`       |
| Servicio Técnico    | `fas fa-tools`       |
| Administración      | `fas fa-cog`         |
| Usuarios            | `fas fa-users-cog`   |
| Subítem genérico    | `far fa-circle`      |

---

## Notas

- El sidebar está configurado con `data-accordion="false"`, lo que permite que múltiples submenús estén abiertos al mismo tiempo.
- El archivo `sidebar.blade.php` incluye un script que detecta la URL activa y aplica `menu-open` a los treeview correspondientes para el caso de navegación directa por URL.
- El color de fondo del sidebar es `#132a56` y el estado activo usa fondo blanco con texto `#132a56`, definido en `sidebar.blade.php`.
