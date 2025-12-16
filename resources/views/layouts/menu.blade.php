<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Home</p>
    </a>
</li>

@can('productos index')
    <li class="nav-item">
        <a href="{{ route('productos.index') }}" class="nav-link {{ Request::is('productos*') ? 'active' : '' }}">
            <i class="fas fa-box-open"></i>
            <p>Productos</p>
        </a>
    </li>
@endcan

@can('stocks index')
    <li class="nav-item">
        <a href="{{ route('stocks.index') }}" class="nav-link {{ Request::is('stocks*') ? 'active' : '' }}">
            <i class="fas fa-box"></i>
            <p>Stocks</p>
        </a>
    </li>
@endcan

@can('ventas index')
    <li class="nav-item">
        <a href="{{ route('ventas.index') }}" class="nav-link {{ Request::is('ventas*') ? 'active' : '' }}">
            <i class="fas fa-shopping-basket"></i>
            <p>Ventas</p>
        </a>
    </li>
@endcan

@can('cuentasacobrar index')
    <li class="nav-item">
        <a href="{{ route('cuentasacobrar.index') }}" class="nav-link {{ Request::is('cuentasacobrar*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i>
            <p>Cuentas a Cobrar</p>
        </a>
    </li>
@endcan

@can('cuentasapagar index')
    <li class="nav-item">
        <a href="{{ route('cuentasapagar.index') }}" class="nav-link {{ Request::is('cuentasapagar*') ? 'active' : '' }}">
            <i class="fas fa-money-bill"></i>
            <p>Cuentas a Pagar</p>
        </a>
    </li>
@endcan

@can('pedidos index')
    <li class="nav-item">
        <a href="{{ route('pedidos.index') }}" class="nav-link {{ Request::is('pedidos*') ? 'active' : '' }}">
            <i class="fas fa-shopping-cart"></i>
            <p>Pedidos</p>
        </a>
    </li>
@endcan

@can('compras index')
    <li class="nav-item">
        <a href="{{ route('compras.index') }}" class="nav-link {{ Request::is('compras*') ? 'active' : '' }}">
            <i class="fas fa-shopping-bag"></i>
            <p>Compras</p>
        </a>
    </li>
@endcan

@can('clientes index')
    <li class="nav-item">
        <a href="{{ route('clientes.index') }}" class="nav-link {{ Request::is('clientes*') ? 'active' : '' }}">
            <i class="fas fa-users"></i>
            <p>Clientes</p>
        </a>
    </li>
@endcan

<!-- Reportes -->
<li
    class="nav-item {{ Request::is('auditoria*') ||
    Request::is('reporte-cargos*') ||
    Request::is('reporte-clientes*') ||
    Request::is('reporte-proveedores*') ||
    Request::is('reporte-productos*') ||
    Request::is('reporte-sucursales*') ||
    Request::is('reporte-ventas*')
        ? 'menu-is-opening menu-open'
        : '' }}
">
    <a href="#" class="nav-link">
        <i class="fas fa-chart-bar"></i>
        <p>
            <i class="fas fa-angle-left right"></i>
            Reportes
        </p>
    </a>
    <ul class="nav nav-treeview"
        style="display: {{ Request::is('auditoria*') ||
        Request::is('reporte-cargos*') ||
        Request::is('reporte-clientes*') ||
        Request::is('reporte-proveedores*') ||
        Request::is('reporte-productos*') ||
        Request::is('reporte-sucursales*') ||
        Request::is('reporte-ventas*')
            ? 'block;'
            : 'none;' }};">
        
        {{-- ELIMINAMOS EL LI INNECESARIO Y DEJAMOS SOLO LOS CAN --}}
        
        @can('auditoria index')
            <li class="nav-item">
                <a href="{{ url('auditoria') }}" class="nav-link {{ Request::is('auditoria*') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <p>Auditoría</p>
                </a>
            </li>
        @endcan

        {{-- PERMISOS CORREGIDOS A 'reporte-cargos' --}}
        @can('reporte-cargos') 
            <li class="nav-item">
                <a href="{{ url('reporte-cargos') }}"
                    class="nav-link {{ Request::is('reporte-cargos*') ? 'active' : '' }}">
                    <i class="fas fa-address-card"></i>
                    <p>Reporte cargos</p>
                </a>
            </li>
        @endcan

        {{-- PERMISOS CORREGIDOS A 'reporte-clientes' --}}
        @can('reporte-clientes')
        <li class="nav-item">
            <a href="{{ url('reporte-clientes') }}"
                class="nav-link {{ Request::is('reporte-clientes*') ? 'active' : '' }}">
                <i class="fas fa-users"></i>
                <p>Reporte clientes</p>
            </a>
        </li>
        @endcan
    
        {{-- PERMISOS CORREGIDOS A 'reporte-proveedores' --}}
        @can('reporte-proveedores')
        <li class="nav-item">
            <a href="{{ url('reporte-proveedores') }}"
                class="nav-link {{ Request::is('reporte-proveedores*') ? 'active' : '' }}">
                <i class="fas fa-archive"></i>
                <p>Reporte proveedores</p>
            </a>
        </li>
        @endcan

        {{-- PERMISOS CORREGIDOS A 'reporte-productos' --}}
        @can('reporte-productos')
        <li class="nav-item">
            <a href="{{ url('reporte-productos') }}"
                class="nav-link {{ Request::is('reporte-productos*') ? 'active' : '' }}">
                <i class="fas fa-box"></i>
                <p>Reporte productos</p>
            </a>
        </li>
        @endcan

        {{-- PERMISOS CORREGIDOS A 'reporte-sucursales' --}}
        @can('reporte-sucursales')
        <li class="nav-item">
            <a href="{{ url('reporte-sucursales') }}"
                class="nav-link {{ Request::is('reporte-sucursales*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <p>Reporte sucursales</p>
            </a>
        </li>
        @endcan

        {{-- PERMISOS CORREGIDOS A 'reporte-ventas' --}}
        @can('reporte-ventas')
        <li class="nav-item">
            <a href="{{ url('reporte-ventas') }}"
                class="nav-link {{ Request::is('reporte-ventas*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <p>Reporte ventas</p>
            </a>
        </li>
        @endcan

         {{-- PERMISOS CORREGIDOS A 'reporte-compras' --}}
        @can('reporte-compras')
        <li class="nav-item">
            <a href="{{ url('reporte-compras') }}"
                class="nav-link {{ Request::is('reporte-compras*') ? 'active' : '' }}">
                <i class="fas fa-chart-line"></i>
                <p>Reporte Compras</p>
            </a>
        </li>
        @endcan

    </ul>
</li>

<!-- Configuraciones -->
<li
    class="nav-item {{ Request::is('users*') ||
    Request::is('cargos*') ||
    Request::is('departamentos*') ||
    Request::is('proveedores*') ||
    Request::is('ciudades*') ||
    Request::is('sucursales*') ||
    Request::is('marcas*') ||
    Request::is('permissions*') ||
    Request::is('cajas*') ||
    Request::is('roles*')
        ? 'menu-is-opening menu-open'
        : '' }}
">
    <a href="#" class="nav-link">
        <i class="fas fa-cogs"></i>
        <p>
            Configuraciones
            <i class="fas fa-angle-left right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview"
        style="display: {{ Request::is('users*') ||
        Request::is('cargos*') ||
        Request::is('departamentos*') ||
        Request::is('proveedores*') ||
        Request::is('ciudades*') ||
        Request::is('sucursales*') ||
        Request::is('marcas*') ||
        Request::is('permissions*') ||
        Request::is('cajas*') ||
        Request::is('roles*')
            ? 'block;'
            : 'none;' }};">

        @can('departamentos index')
            <li class="nav-item">
                <a href="{{ route('departamentos.index') }}"
                    class="nav-link {{ Request::is('departamentos*') ? 'active' : '' }}">
                    <i class="fas fa-align-justify"></i>
                    <p>Departamentos</p>
                </a>
            </li>
        @endcan

        @can('ciudades index')
            <li class="nav-item">
                <a href="{{ route('ciudades.index') }}" class="nav-link {{ Request::is('ciudades*') ? 'active' : '' }}">
                    <i class="fas fa-address-book"></i>
                    <p>Ciudades</p>
                </a>
            </li>
        @endcan

        @can('sucursales index')
            <li class="nav-item">
                <a href="{{ route('sucursales.index') }}"
                    class="nav-link {{ Request::is('sucursales*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <p>Sucursales</p>
                </a>
            </li>
        @endcan

        @can('cajas index')
            <li class="nav-item">
                <a href="{{ route('cajas.index') }}" class="nav-link {{ Request::is('cajas*') ? 'active' : '' }}">
                    <i class="fas fa-cash-register"></i>
                    <p>Cajas</p>
                </a>
            </li>
        @endcan

        @can('cargos index')
            <li class="nav-item">
                <a href="{{ route('cargos.index') }}" class="nav-link {{ Request::is('cargos*') ? 'active' : '' }}">
                    <i class="fas fa-address-card"></i>
                    <p>Cargos</p>
                </a>
            </li>
        @endcan

        @can('marcas index')
            <li class="nav-item">
                <a href="{{ route('marcas.index') }}" class="nav-link {{ Request::is('marcas*') ? 'active' : '' }}">
                    <i class="fa fa-tag"></i>
                    <p>Marcas</p>
                </a>
            </li>
        @endcan

        @can('proveedores index')
            <li class="nav-item">
                <a href="{{ route('proveedores.index') }}"
                    class="nav-link {{ Request::is('proveedores*') ? 'active' : '' }}">
                    <i class="fas fa-archive"></i>
                    <p>Proveedores</p>
                </a>
            </li>
        @endcan

        @can('users index')
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <p>Usuarios</p>
                </a>
            </li>
        @endcan

        @can('permissions index')
            <li class="nav-item">
                <a href="{{ route('permissions.index') }}"
                    class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <p>Permisos</p>
                </a>
            </li>
        @endcan

        @can('roles index')
            <li class="nav-item">
                <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
                    <i class="fa fa-user-tag"></i>
                    <p>Roles</p>
                </a>
            </li>
        @endcan

    </ul>
</li>