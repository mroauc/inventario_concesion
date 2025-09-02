# Resumen del Sistema - Inventario Concesión

## Información General del Proyecto

### Versión de Laravel
- **Laravel Framework**: 8.83.27
- **PHP**: ^7.3|^8.0

### Dependencias Principales

#### Composer (composer.json)
- **laravel/framework**: ^8.75
- **infyomlabs/adminlte-templates**: ^3.0 (Templates AdminLTE)
- **maatwebsite/excel**: ^3.1 (Importación/exportación Excel)
- **laravel/ui**: ^3.4 (Autenticación UI)
- **laravelcollective/html**: ^6.4 (Formularios HTML)
- **laravel/sanctum**: ^2.11 (API tokens)
- **guzzlehttp/guzzle**: ^7.0.1 (Cliente HTTP)

#### NPM (package.json)
- **bootstrap**: ^5.1.3
- **axios**: ^0.21
- **laravel-mix**: ^6.0.6
- **sass**: ^1.32.11

### Estructura de Carpetas Relevantes
```
app/
├── Http/Controllers/     # Controladores
├── Models/              # Modelos Eloquent
├── Repositories/        # Patrón Repository
├── Imports/            # Clases de importación Excel
└── Http/Requests/      # Form Requests

database/
├── migrations/         # Migraciones de BD
├── factories/         # Factories para testing
└── seeders/          # Seeders

resources/
├── views/            # Vistas Blade
└── js/              # JavaScript

routes/
├── web.php          # Rutas web
└── api.php          # Rutas API
```

## Base de Datos

### Tablas Principales
1. **concessions** - Concesiones/empresas
2. **users** - Usuarios del sistema
3. **representatives** - Representantes de concesiones
4. **category_products** - Categorías de productos
5. **products** - Productos
6. **stores** - Bodegas/almacenes
7. **product_stores** - Tabla pivot productos-bodegas (con stock)
8. **positions_product_store** - Posiciones de productos en bodegas
9. **user_concession** - Tabla pivot usuarios-concesiones
10. **logs** - Registro de actividades
11. **password_resets** - Reseteo de contraseñas
12. **failed_jobs** - Jobs fallidos
13. **personal_access_tokens** - Tokens de acceso

### Relaciones Principales
- **Concession** → hasMany Users (a través de pivot)
- **Product** → belongsTo Category_product
- **Product** → belongsToMany Store (pivot: product_stores)
- **Store** → belongsToMany Product (pivot: product_stores)
- **User** → belongsToMany Concession (pivot: user_concession)
- **Product_Store** → hasMany Positions_product_store

### Tablas Pivot/Intermedias
- **product_stores**: Relaciona productos con bodegas, incluye stock
- **user_concession**: Relaciona usuarios con concesiones
- **positions_product_store**: Posiciones específicas de productos en bodegas

## Modelos

### Modelos Principales y Relaciones
1. **Product**
   - belongsTo Category_product
   - belongsToMany Store (warehouses)
   - Método: stock_total() - calcula stock total

2. **Store**
   - belongsToMany Product
   - SoftDeletes habilitado

3. **User** (Authenticatable)
   - belongsToMany Concession
   - HasApiTokens, Notifiable

4. **Product_Store** (Pivot)
   - belongsTo Product, Store, User (responsible)
   - hasMany Positions_product_store

5. **Concession**
   - belongsTo Representative (User)
   - belongsToMany User
   - SoftDeletes habilitado

6. **Category_product**
   - SoftDeletes habilitado

### Atributos Destacados
- **SoftDeletes**: Product, Store, Concession, Category_product
- **Fillable**: Todos los modelos tienen campos fillable definidos
- **Casts**: Principalmente string para campos de texto
- **Validation rules**: Definidas en modelos con $rules

## Controladores

### Controladores Principales
1. **ProductController**
   - CRUD completo de productos
   - getProduct(): Obtiene info de producto en bodega específica
   - storeModal(): Actualiza stock y posición desde modal
   - Importación de productos (Excel/CSV)
   - Logging de actividades

2. **HomeController**
   - Dashboard principal
   - Muestra bodegas de la concesión del usuario

3. **StoreController**
   - Gestión de bodegas/almacenes

4. **Category_productController**
   - Gestión de categorías de productos

5. **ConcessionController**
   - Gestión de concesiones

6. **UserController**
   - Gestión de usuarios

7. **RepresentativeController**
   - Gestión de representantes

8. **LogsController**
   - Visualización de historial de actividades

### Enfoque de Controladores
- **Inventario**: ProductController, StoreController
- **Catalogación**: Category_productController
- **Administración**: UserController, ConcessionController
- **Auditoría**: LogsController
- **Importación**: Funciones en ProductController

## Vistas (Blade)

### Vistas Principales
- **home.blade.php**: Dashboard principal
- **modal_inventario.blade.php**: Modal para editar inventario
- **table_inventario.blade.php**: Tabla de inventario

### Estructura por Módulo
- **products/**: CRUD de productos, importación
- **stores/**: CRUD de bodegas
- **category_products/**: CRUD de categorías
- **users/**: CRUD de usuarios
- **concessions/**: CRUD de concesiones
- **representative/**: CRUD de representantes
- **logs/**: Visualización de logs
- **auth/**: Login, registro, recuperación

### Layouts y Componentes
- **layouts/app.blade.php**: Layout principal
- **layouts/menu.blade.php**: Menú de navegación
- **layouts/sidebar.blade.php**: Barra lateral
- Cada módulo tiene: create, edit, index, show, fields, table

## Middlewares y Policies

### Middlewares Implementados
- **auth**: Autenticación requerida
- **guest**: Solo usuarios no autenticados
- **throttle**: Limitación de requests
- **verified**: Email verificado
- Middlewares globales: CORS, CSRF, sesiones

### Uso de Middlewares
- HomeController usa middleware 'auth'
- Rutas de usuarios requieren middleware 'auth'
- No se identificaron policies personalizadas

## Jobs, Events, Listeners

### Procesos en Background
- Configuración para **failed_jobs** table
- No se identificaron jobs personalizados implementados

### Eventos y Listeners
- No se identificaron eventos o listeners personalizados
- Sistema usa eventos estándar de Laravel

## Rutas

### Rutas Principales (web.php)
- **/** → HomeController@index (dashboard)
- **Resource routes**:
  - products → ProductController
  - stores → StoreController  
  - users → UserController
  - concessions → ConcessionController
  - categoryProducts → Category_productController

### Rutas Específicas
- **/product/getInfo** → Obtener info de producto
- **/product/storeModal** → Actualizar desde modal
- **/product/importar** → Importación de productos
- **/historial** → Logs del sistema
- **/representative/** → CRUD representantes

### Rutas API
- Configuradas pero no implementadas específicamente

## Servicios Externos

### Integraciones
- **Maatwebsite/Excel**: Importación/exportación de archivos Excel y CSV
- **AdminLTE**: Templates de interfaz
- **Bootstrap 5**: Framework CSS

### Almacenamiento
- Configuración estándar de Laravel (local, public)
- No se identifican integraciones con S3 u otros servicios cloud

## Conclusión

### Flujo Principal del Sistema
1. **Autenticación**: Usuario se autentica y accede según su concesión
2. **Gestión de Productos**: Creación/edición de productos con categorías
3. **Gestión de Bodegas**: Configuración de almacenes por concesión
4. **Control de Inventario**: 
   - Productos se asignan a bodegas con stock específico
   - Posiciones detalladas dentro de cada bodega
   - Actualización de stock mediante modales
5. **Importación Masiva**: Carga de productos via Excel/CSV
6. **Auditoría**: Registro completo de actividades en tabla logs
7. **Multi-tenancy**: Sistema segmentado por concesiones

### Observaciones Relevantes
- **Patrón Repository**: Implementado para abstracción de datos
- **Multi-tenancy**: Sistema diseñado para múltiples concesiones
- **Soft Deletes**: Implementado en modelos principales
- **Logging Completo**: Todas las operaciones CRUD se registran
- **Importación Flexible**: Soporte para Excel y CSV
- **Gestión de Posiciones**: Control detallado de ubicación de productos
- **Interfaz AdminLTE**: UI moderna y responsiva
- **Validación**: Form Requests para validación de datos
- **Transacciones**: Uso de DB transactions para integridad
- **Seguridad**: Middleware de autenticación y CSRF protection