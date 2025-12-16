<x-laravel-ui-adminlte::adminlte-layout>

    {{-- Estilos personalizados para "hermosear" el login --}}
    <style>
        /* 1. Fondo de pantalla profesional */
        body.login-page {
            /* Opción A: Degradado Corporativo (Azul/Morado moderno) */
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
            
            /* Opción B: Si prefieres una imagen de fondo (descomenta la siguiente linea y pon tu url) */
            /* background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('ruta/a/tu/imagen.jpg') no-repeat center center fixed !important; background-size: cover !important; */
            
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }

        /* 2. Caja del Login (La tarjeta) */
        .login-box {
            width: 400px; /* Un poco más ancho para elegancia */
            margin: 0;
        }

        .card {
            border: none;
            border-radius: 20px; /* Bordes muy redondeados */
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3) !important; /* Sombra profunda */
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95); /* Ligeramente traslúcido */
            backdrop-filter: blur(10px); /* Efecto cristal */
        }

        /* 3. Cabecera del Login (Logo) */
        .login-logo {
            margin-bottom: 0;
            padding: 30px 20px 10px;
            text-align: center;
        }
        .login-logo a {
            color: #1e3c72 !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 28px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .login-card-body {
            padding: 30px 40px 40px;
            border-radius: 0 !important;
            background: transparent;
        }

        .login-box-msg {
            color: #666;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        /* 4. Inputs (Campos de texto) */
        .input-group {
            background: #f4f6f9;
            border-radius: 50px; /* Campos redondos */
            padding: 5px 20px;
            border: 1px solid #e0e0e0;
            margin-bottom: 20px !important;
            transition: all 0.3s ease;
        }

        .input-group:focus-within {
            border-color: #2a5298;
            box-shadow: 0 0 0 4px rgba(42, 82, 152, 0.1);
            background: #fff;
        }

        .form-control {
            border: none;
            background: transparent !important;
            height: 40px;
            padding-left: 10px;
            font-size: 15px;
            color: #333;
        }
        
        .form-control:focus {
            box-shadow: none; /* Quitamos el brillo azul defecto de bootstrap */
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #2a5298; /* Color de los iconos */
            font-size: 1.2rem;
        }

        /* 5. Botón de Acceder */
        .btn-primary {
            background: linear-gradient(90deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            border-radius: 50px;
            height: 45px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 5px 15px rgba(42, 82, 152, 0.4);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px); /* Efecto flotante */
            box-shadow: 0 8px 20px rgba(42, 82, 152, 0.5);
            background: linear-gradient(90deg, #2a5298 0%, #1e3c72 100%);
        }

        /* Enlaces (Olvidé contraseña / Registrarse) */
        .auth-links a {
            color: #666;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .auth-links a:hover {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 600;
        }
        
        /* Manejo de errores visual */
        .is-invalid {
            color: #dc3545;
        }
        .input-group.has-error {
            border-color: #dc3545;
            background: #fff8f8;
        }
    </style>

    <body class="hold-transition login-page">
        <div class="login-box">
            
            <!-- Tarjeta Principal -->
            <div class="card">
                
                <!-- Cabecera con Logo -->
                <div class="login-logo">
                    {{-- Icono decorativo opcional --}}
                    <div style="font-size: 3rem; color: #2a5298; margin-bottom: 10px;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <a href="{{ url('/home') }}"><b>{{ config('app.name') }}</b></a>
                </div>

                <div class="card-body login-card-body">
                    <p class="login-box-msg">Bienvenido</p>

                    {{-- Mensajes flash (errores, alertas) --}}
                    @include('flash::message')

                    <form method="post" action="{{ url('/login') }}">
                        @csrf

                        {{-- Input Email --}}
                        <div class="input-group @error('email') has-error @enderror">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-envelope"></span>
                                </div>
                            </div>
                            <input type="text" name="email" value="{{ old('email') }}" placeholder="Correo Electrónico"
                                class="form-control @error('email') is-invalid @enderror" required autofocus>
                        </div>
                        {{-- Mensaje de error separado para no romper el diseño del input --}}
                        @error('email')
                            <span class="d-block text-danger small mb-3 text-center" style="margin-top: -15px;">
                                {{ $message }}
                            </span>
                        @enderror

                        {{-- Input Password --}}
                        <div class="input-group @error('password') has-error @enderror">
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-lock"></span>
                                </div>
                            </div>
                            <input type="password" name="password" placeholder="Contraseña"
                                class="form-control @error('password') is-invalid @enderror" required>
                        </div>
                        @error('password')
                            <span class="d-block text-danger small mb-3 text-center" style="margin-top: -15px;">
                                {{ $message }}
                            </span>
                        @enderror

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">
                                    INGRESAR <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="auth-links text-center mt-4">
                        <p class="mb-2">
                            <a href="{{ route('register') }}">
                                ¿No tienes cuenta? <b style="color: #2a5298">Regístrate aquí</b>
                            </a>
                        </p>
                        {{-- 
                        <p class="mb-0">
                            <a href="{{ route('password.request') }}">Olvidé mi contraseña</a>
                        </p> 
                        --}}
                    </div>
                </div>
            </div>
        </div>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>