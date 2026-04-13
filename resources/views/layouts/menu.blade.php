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
        <li class="nav-item">
            <a href="{{ route('products.index') }}"
               class="nav-link {{ Request::is('products*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Productos</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('stores.index') }}"
               class="nav-link {{ Request::is('stores*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Bodegas</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('categoryProducts.index') }}"
               class="nav-link {{ Request::is('categoryProducts*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Categorías</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('products.index_importar') }}"
               class="nav-link {{ Request::is('product/importar*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Importar Productos</p>
            </a>
        </li>
    </ul>
</li>

{{-- =============================================
     SERVICIO TÉCNICO (Submenu)
     ============================================= --}}
@php
    $servicioActivo = Request::is('ordenes_servicio*') || Request::is('tecnicos*') || Request::is('clientes*') || Request::is('servicios*');
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
        <li class="nav-item">
            <a href="{{ route('ordenes_servicio.index') }}"
               class="nav-link {{ Request::is('ordenes_servicio*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Órdenes de Servicio</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('clientes.index') }}"
               class="nav-link {{ Request::is('clientes*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Clientes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('tecnicos.index') }}"
               class="nav-link {{ Request::is('tecnicos*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Técnicos</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('servicios.index') }}"
               class="nav-link {{ Request::is('servicios*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Servicios</p>
            </a>
        </li>
    </ul>
</li>

{{-- =============================================
     ADMINISTRACIÓN (Submenu)
     ============================================= --}}
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
        <li class="nav-item">
            <a href="{{ route('concessions.index') }}"
               class="nav-link {{ Request::is('concessions*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Concesiones</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('representative.index') }}"
               class="nav-link {{ Request::is('representative*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Representantes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('logs.index') }}"
               class="nav-link {{ Request::is('historial*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Historial</p>
            </a>
        </li>
    </ul>
</li>

{{-- =============================================
     USUARIOS (solo admin)
     ============================================= --}}
@if (auth()->user()->email == 'marceloroa19@gmail.com')
    <li class="nav-item">
        <a href="{!! route('users.index') !!}"
           class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
            <i class="nav-icon fas fa-users-cog"></i>
            <p>Usuarios</p>
        </a>
    </li>
@endif
