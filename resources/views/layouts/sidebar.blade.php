<style>
    .liEmpresaActiva{
        list-style: none;
        padding: 0rem 1rem;
        position: relative;
    }

    .liEmpresaActiva span{
        color: #fff;
    }

    .contNombreEmpresa{
        text-align: center;
        border-bottom: 1px solid #4b545c;
        color: #868a8d;
        font-weight: 300;
        font-size: larger;
    }

    .iconoCambiar{
        color: #c1c1c1;
        position: absolute;
        right: 10%;
        top: 3px;
        transform: scale(1.2);
        cursor: pointer;
    }

    .iconoCambiar:hover{
        transform: scale(1.3);
    }
    
    .sub-menu{
        display: none;
    }

    .sub-menu > a{
        padding-left: 35px;
    }

    .has-treeview ul{
        background: rgba(94, 97, 100, 0.349) !important;
    }

    .nav-item{
        cursor: pointer;
    }

    .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active, .sidebar-light-primary .nav-sidebar>.nav-item>.nav-link.active {
        background-color: #ffffff;
        color: #132a56;
    }

    .btn-primary{
        background-color: #132a56;
        border-color: #132a56;
    }

    .btn-primary:hover{
        color: #132a56;
        background-color: #fff;
        border-color: #132a56;
    }
</style>

    <aside class="main-sidebar sidebar-dark-primary elevation-4" style="overflow-x: hidden; color: white;">
        <a href="{{ route('home') }}" class="brand-link" style="margin-left: 5px">
            <span class="brand-text font-weight-light">INVENTARIO</span>
        </a>
        <div class="liEmpresaActiva brand-text">
            <li>
                <span>Empresa Activa:</span>
                <div class="contNombreEmpresa">
                    <span>ROAVAL</span>
                </div>
                {{-- @if(auth()->user()->empresa_activa !== null)
                    <div class="contNombreEmpresa">
                        @if(auth()->user()->empresa_activa !== null)
                            <span>{{auth()->user()->empresa_activa->nombre}}</span>
                        @endif
                    </div>
                @endif --}}
            </li>
            <div class="iconoCambiar">
                <a class="fas fa-exchange-alt" data-toggle="modal" data-target="#myModal"></a>
            </div>
        </div>
        
        <div class="sidebar" id="sidebar" data-api="tree" data-accordion=0 style="overflow: hidden; height: calc(100vh - 130px); margin-right: 1px">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    @include('layouts.menu')
                    {{-- <div id="menuERP">
                        @include('layouts.menu')
                    </div>
                    <div id="menuCRM" style="display: none">
                        @include('layouts.menu_crm')
                    </div> --}}
                </ul>
            </nav>
        </div>
    </aside>

@push('page_scripts')
    <script>
        var url = window.location;
        // for treeview
        $('ul.nav-treeview a').filter(function() {
            return this.href == url;
        }).parentsUntil(".nav-sidebar > .nav-treeview").addClass('menu-open').prev('a').addClass('active');

        $('.main-sidebar').hover(
            function() {
                $('#sidebar').css('overflow', 'auto'); }, 
            function() { 
                $('#sidebar').css('overflow', 'hidden'); }
        );
    </script>
@endpush
