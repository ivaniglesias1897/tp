<x-laravel-ui-adminlte::adminlte-layout>

    <head>
        <title>LP@2</title>
        <link rel="icon" type="image/x-icon" href="">
        <!-- AdminLTE CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
        <!-- librerias css select2 -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
        <!-- personalizar estilos de select 2  -->
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
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 35px;
                position: absolute;
                top: 1px;
                right: 1px;
                width: 20px;
            }
        </style>
        <!-- cargar estilos css desde los blade -->
        @stack('styles')
    </head>

    <body class="hold-transition sidebar-mini layout-fixed">
        <div class="wrapper">
            <!-- Main Header -->
            <nav class="main-header navbar navbar-expand navbar-white navbar-light">
                <!-- Left navbar links -->
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                            <i class="fas fa-bars"></i>
                        </a>
                    </li>
                </ul>

                <ul class="ml-auto navbar-nav">
                    <li class="nav-item dropdown user-menu">
                        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            <img src="https://assets.infyom.com/logo/blue_logo_150x150.png"
                                 class="user-image img-circle elevation-2" alt="User Image">
                            <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                            <!-- User image -->
                            <li class="user-header bg-primary">
                                <img src="https://assets.infyom.com/logo/blue_logo_150x150.png"
                                     class="img-circle elevation-2" alt="User Image">
                                <p>
                                    {{ Auth::user()->name }}
                                    <small>Member since {{ Auth::user()->created_at->format('M. Y') }}</small>
                                </p>
                            </li>
                            <!-- Menu Footer-->
                            <li class="user-footer">
                                <a href="{{ url('perfil') }}" class="btn btn-default btn-flat">Perfil</a>
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

            <!-- Left side column. contains the logo and sidebar -->
            @include('layouts.sidebar')

            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                @yield('content')
            </div>

            <!-- Main Footer -->
            <footer class="main-footer">
                <div class="float-right d-none d-sm-block">
                    <b>Copyright</b> 3.2.0
                </div>
                <strong>Copyright &copy; 2025 <a href="javascript:void(0)">Curso LP 3</a>.</strong> UTIC.
            </footer>
        </div>

        <!-- REQUIRED SCRIPTS -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
        <!-- AdminLTE App (bundle completo 3.2.0) -->
        <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!-- librerias js select2 -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

        <!-- cargar codigo javascript desde los blade -->
        @stack('scripts')

        <!-- CUSTOM SCRIPTS -->
        <script>
            $(document).ready(function() {
                // Inicializar dropdowns de Bootstrap explícitamente
                $('.dropdown-toggle').dropdown();

                // Asegurar que los dropdowns funcionen correctamente
                $('.dropdown-toggle').on('click', function(e) {
                    e.preventDefault();
                    $(this).next('.dropdown-menu').toggle();
                });

                // Cerrar dropdown al hacer click fuera
                $(document).on('click', function(e) {
                    if (!$(e.target).closest('.dropdown').length) {
                        $('.dropdown-menu').hide();
                    }
                });

                // Inicializar select2 en los elementos con la clase .select2
                $('.select2').select2({
                    placeholder: "Selecciona una opción",
                    allowClear: true,
                    width: '100%'
                });

                //sweetalert para confirmacion de borrado
                $('.alert-delete').click(function(event) {
                    var form = $(this).closest("form");
                    event.preventDefault();
                    let valor = $(this).data("mensaje") || "este registro";
                    Swal.fire({
                            title: "Atención",
                            text: `Desea borrar ${valor}?`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: "Confirmar",
                            cancelButtonText: "Cancelar",
                        })
                        .then(resultado => {
                            if (resultado.value) {
                                form.submit();
                            }
                        });
                });

                /** bucador mediante peticiones fetch*/
                $('.buscar').on('keyup', function() {
                    var query = this.value;
                    var url = this.getAttribute('data-url');
                    fetch(url + '?buscar=' + encodeURIComponent(query), {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            if (!response.ok) {
                                alert('Error en la consulta');
                                throw new Error('Error en la respuesta del servidor');
                            }
                            return response.text();
                        })
                        .then(data => {
                            $('.tabla-container').html(data);
                        })
                        .catch(error => {
                            console.error('Hubo un problema con la solicitud Fetch:', error);
                        });
                });
            });

            //formato de numeros separador de miles
            function format(input) {
                var num = input.value.replace(/\./g, '');
                if (!isNaN(num)) {
                    num = num.split('').reverse().join('')
                        .replace(/(\d{3})(?=\d)/g, '$1.')
                        .split('').reverse().join('');
                    input.value = num;
                } else {
                    alert("Por favor, introduce un número válido");
                    input.value = input.value.replace(/[^\d]/g, '');
                }
            }
        </script>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>