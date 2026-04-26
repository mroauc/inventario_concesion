{{-- =============================================
     INICIO
     ============================================= --}}
<li class="nav-item">
    <a href="{{ route('home') }}"
       class="nav-link {{ Request::is('/') || Request::is('home*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Inicio</p>
    </a>
</li>

{{-- =============================================
     INVENTARIO (Submenu)
     ============================================= --}}
@canany(['productos.ver', 'bodegas.ver', 'categorias.ver'])
@php
    $inventarioActivo = Request::is('products*') || Request::is('stores*') || Request::is('categoryProducts*') || Request::is('product/importar*');
@endphp
<li class="nav-item has-treeview {{ $inventarioActivo ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $inventarioActivo ? 'active' : '' }}">
        <i class="nav-icon fas fa-boxes"></i>
        <p>
            Inventario
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @can('productos.ver')
        <li class="nav-item">
            <a href="{{ route('products.index') }}"
               class="nav-link {{ Request::is('products*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Productos</p>
            </a>
        </li>
        @endcan
        @can('bodegas.ver')
        <li class="nav-item">
            <a href="{{ route('stores.index') }}"
               class="nav-link {{ Request::is('stores*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Bodegas</p>
            </a>
        </li>
        @endcan
        @can('categorias.ver')
        <li class="nav-item">
            <a href="{{ route('categoryProducts.index') }}"
               class="nav-link {{ Request::is('categoryProducts*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Categorías</p>
            </a>
        </li>
        @endcan
        @can('productos.importar')
        <li class="nav-item">
            <a href="{{ route('products.index_importar') }}"
               class="nav-link {{ Request::is('product/importar*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Importar Productos</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

{{-- =============================================
     SERVICIO TÉCNICO (Submenu)
     ============================================= --}}
@canany(['ordenes.ver', 'clientes.ver', 'tecnicos.ver', 'servicios.ver', 'artefactos.ver', 'tipo_artefactos.ver'])
@php
    $servicioActivo = Request::is('ordenes_servicio*') || Request::is('tecnicos*') || Request::is('clientes*') || Request::is('servicios*') || Request::is('artefactos*') || Request::is('tipo_artefactos*');
@endphp
<li class="nav-item has-treeview {{ $servicioActivo ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $servicioActivo ? 'active' : '' }}">
        <i class="nav-icon fas fa-tools"></i>
        <p>
            Servicio Técnico
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @can('ordenes.ver')
        <li class="nav-item">
            <a href="{{ route('ordenes_servicio.index') }}"
               class="nav-link {{ Request::is('ordenes_servicio*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Órdenes de Servicio</p>
            </a>
        </li>
        @endcan
        @can('clientes.ver')
        <li class="nav-item">
            <a href="{{ route('clientes.index') }}"
               class="nav-link {{ Request::is('clientes*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Clientes</p>
            </a>
        </li>
        @endcan
        @can('tecnicos.ver')
        <li class="nav-item">
            <a href="{{ route('tecnicos.index') }}"
               class="nav-link {{ Request::is('tecnicos*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Técnicos</p>
            </a>
        </li>
        @endcan
        @can('servicios.ver')
        <li class="nav-item">
            <a href="{{ route('servicios.index') }}"
               class="nav-link {{ Request::is('servicios*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Servicios</p>
            </a>
        </li>
        @endcan
        @can('artefactos.ver')
        <li class="nav-item">
            <a href="{{ route('artefactos.index') }}"
               class="nav-link {{ Request::is('artefactos') || Request::is('artefactos/create') || Request::is('artefactos/*/edit') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Artefactos</p>
            </a>
        </li>
        @endcan
        @can('artefactos.importar')
        <li class="nav-item">
            <a href="{{ route('artefactos.index_importar') }}"
               class="nav-link {{ Request::is('artefactos/importar') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Importar Artefactos</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('artefactos.historial') }}"
               class="nav-link {{ Request::is('artefactos/historial-importacion') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Historial Importación</p>
            </a>
        </li>
        @endcan
        @can('tipo_artefactos.ver')
        <li class="nav-item">
            <a href="{{ route('tipo_artefactos.index') }}"
               class="nav-link {{ Request::is('tipo_artefactos*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Tipos de Artefacto</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

{{-- =============================================
     FLUJO DE CAJA
     ============================================= --}}
@can('flujo_caja.ver')
<li class="nav-item">
    <a href="{{ route('flujo_caja.index') }}"
       class="nav-link {{ Request::is('flujo-caja*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cash-register"></i>
        <p>Flujo de Caja</p>
    </a>
</li>
@endcan

{{-- =============================================
     ADMINISTRACIÓN (Submenu)
     ============================================= --}}
@canany(['concesiones.ver', 'representantes.ver', 'historial.ver'])
@php
    $adminActivo = Request::is('concessions*') || Request::is('representative*') || Request::is('historial*');
@endphp
<li class="nav-item has-treeview {{ $adminActivo ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ $adminActivo ? 'active' : '' }}">
        <i class="nav-icon fas fa-cog"></i>
        <p>
            Administración
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @can('concesiones.ver')
        <li class="nav-item">
            <a href="{{ route('concessions.index') }}"
               class="nav-link {{ Request::is('concessions*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Concesiones</p>
            </a>
        </li>
        @endcan
        @can('representantes.ver')
        <li class="nav-item">
            <a href="{{ route('representative.index') }}"
               class="nav-link {{ Request::is('representative*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Representantes</p>
            </a>
        </li>
        @endcan
        @can('historial.ver')
        <li class="nav-item">
            <a href="{{ route('logs.index') }}"
               class="nav-link {{ Request::is('historial*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Historial</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

{{-- =============================================
     ROLES Y PERMISOS (solo super_admin)
     ============================================= --}}
@role('super_admin')
<li class="nav-item">
    <a href="{{ route('roles.index') }}"
       class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-shield-alt"></i>
        <p>Roles y Permisos</p>
    </a>
</li>
@endrole

{{-- =============================================
     USUARIOS
     ============================================= --}}
@can('usuarios.ver')
<li class="nav-item">
    <a href="{!! route('users.index') !!}"
       class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users-cog"></i>
        <p>Usuarios</p>
    </a>
</li>
@endcan
