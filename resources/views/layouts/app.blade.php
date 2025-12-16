<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LP3</title>
    <link rel="icon" type="image/x-icon" href="">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .select2-container .select2-selection--single {
            box-sizing: border-box;
            cursor: pointer;
            display: block;
            height: 38px;
            user-select: none;
            -webkit-user-select: none;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            box-sizing: border-box;
            list-style: none;
            margin: 0;
            padding: 4px 5px;
            width: 100%;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #3c8dbc;
            border-color: #367fa9;
            padding: 1px 10px;
            color: #fff;
        }

        .select2-container--default .select2-selection--single {
            background-color: #fff;
            border-radius: 3px;
            border: 1px solid #aaa;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 35px;
            position: absolute;
            top: 1px;
            right: 1px;
            width: 20px;
        }

        /* Corrección para asegurar que el dropdown se muestre encima de todo */
        .dropdown-menu {
            z-index: 9999 !important;
        }

        /* --- NUEVO: Estilos para la campanita y el badge --- */
        .navbar-badge {
            font-size: .6rem;
            font-weight: 300;
            padding: 2px 4px;
            position: absolute;
            right: 5px;
            top: 9px;
        }
    </style>
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i
                            class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">

                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        {{-- Solo mostramos el badge si hay notificaciones --}}
                        @if (isset($totalNotificaciones) && $totalNotificaciones > 0)
                            <span class="badge badge-warning navbar-badge">{{ $totalNotificaciones }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">{{ $totalNotificaciones ?? 0 }}
                            Notificaciones</span>

                        @if (isset($alertasStock) && count($alertasStock) > 0)
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-item dropdown-header text-danger font-weight-bold">Stock
                                Crítico</span>
                            @foreach (array_slice($alertasStock, 0, 3) as $stock)
                                {{-- Mostramos solo los primeros 3 --}}
                                <a href="{{ url('reporte-productos') }}" class="dropdown-item">
                                    <i class="fas fa-exclamation-triangle mr-2 text-danger"></i>
                                    {{ Str::limit($stock->descripcion, 15) }}
                                    <span class="float-right text-muted text-sm">{{ $stock->total }} un.</span>
                                </a>
                            @endforeach
                        @endif

                        @if (isset($alertasVencimientos) && count($alertasVencimientos) > 0)
                            <div class="dropdown-divider"></div>
                            <span class="dropdown-item dropdown-header text-warning font-weight-bold">Vencimientos
                                Próximos</span>
                            @foreach (array_slice($alertasVencimientos, 0, 3) as $venc)
                                <a href="{{ url('cuentasacobrar') }}?buscar={{ $venc->cliente }}" class="dropdown-item">
                                    <i class="fas fa-clock mr-2 text-warning"></i> {{ Str::limit($venc->cliente, 12) }}
                                    <span
                                        class="float-right text-muted text-sm">{{ \Carbon\Carbon::parse($venc->vencimiento)->format('d/m') }}</span>
                                </a>
                            @endforeach
                        @endif

                        <div class="dropdown-divider"></div>

                        @if (
                            (!isset($alertasStock) || count($alertasStock) == 0) &&
                                (!isset($alertasVencimientos) || count($alertasVencimientos) == 0))
                            <a href="#" class="dropdown-item dropdown-footer">Sin novedades</a>
                        @else
                            <a href="{{ url('home') }}" class="dropdown-item dropdown-footer">Ver todas las
                                notificaciones</a>
                        @endif
                    </div>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <img src="https://assets.infyom.com/logo/blue_logo_150x150.png"
                            class="user-image img-circle elevation-2" alt="User Image">
                        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <li class="user-header bg-primary">
                            <img src="https://assets.infyom.com/logo/blue_logo_150x150.png"
                                class="img-circle elevation-2" alt="User Image">
                            <p>
                                {{ Auth::user()->name }}
                                <small>Miembro desde {{ Auth::user()->created_at->format('M. Y') }}</small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <a href="{{ route('user.perfil') }}" class="btn btn-default btn-flat">Perfil</a>
                            <a href="#" class="btn btn-default btn-flat float-right"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Salir
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        @include('layouts.sidebar')

        <div class="content-wrapper">
            @yield('content')
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Versión</b> 3.1.0
            </div>
            <strong>Copyright &copy; 2025 <a href="javascript:void(0)">Curso LP 3</a>.</strong> UTIC.
        </footer>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    @stack('scripts')

    <script>
        $(document).ready(function() {

            // Inicializar select2
            $('.select2').select2({
                placeholder: "Selecciona una opción",
                allowClear: true,
                width: '100%'
            });

            // Sweetalert global mejorado para Borrado Lógico y Físico
            $('.alert-delete').click(function(event) {
                var form = $(this).closest("form");
                event.preventDefault();

                // 1. Obtenemos el mensaje del botón data-mensaje
                let valor = $(this).data("mensaje") || "este registro";

                // 2. Lógica Inteligente:
                // Convertimos a minúsculas para comparar
                let valorLower = valor.toLowerCase();
                let textoPregunta = "";

                // Si el mensaje YA empieza con una acción explícita (activar o inactivar)...
                if (valorLower.startsWith('activar') || valorLower.startsWith('inactivar')) {
                    // ...usamos la frase tal cual (Ej: "¿Desea inactivar el cargo Cajero?")
                    textoPregunta = `¿Desea ${valor}?`;
                } else {
                    // ...si no, asumimos que es un borrado físico antiguo y agregamos "borrar"
                    // (Ej: "¿Desea borrar el producto X?")
                    textoPregunta = `¿Desea borrar ${valor}?`;
                }

                Swal.fire({
                        title: "Atención",
                        text: textoPregunta,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: "Confirmar",
                        cancelButtonText: "Cancelar",
                        confirmButtonColor: '#3085d6', // Opcional: azul estándar
                        cancelButtonColor: '#d33', // Opcional: rojo estándar
                    })
                    .then(resultado => {
                        if (resultado.isConfirmed) {
                            form.submit();
                        }
                    });
            });

            // Buscador Fetch
            $('.buscar').on('keyup', function() {
                var query = this.value;
                var url = this.getAttribute('data-url');

                if (url) {
                    fetch(url + '?buscar=' + encodeURIComponent(query), {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Error en la respuesta');
                            return response.text();
                        })
                        .then(data => {
                            $('.tabla-container').html(data);
                        })
                        .catch(error => {
                            console.error('Fetch error:', error);
                        });
                }
            });
        });

        // Formato de miles
        function format(input) {
            var num = input.value.replace(/\./g, '');
            if (!isNaN(num)) {
                num = num.split('').reverse().join('').replace(/(\d{3})(?=\d)/g, '$1.').split('').reverse().join('');
                input.value = num;
            } else {
                input.value = input.value.replace(/[^\d]/g, '');
            }
        }
    </script>

    {{-- Alertas del sistema --}}
    @include('sweetalert::alert')

</body>

</html>
