<x-laravel-ui-adminlte::adminlte-layout>

    {{-- Estilos personalizados (Idénticos al Login para consistencia de marca) --}}
    <style>
        /* 1. Fondo de pantalla profesional */
        body.register-page {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%) !important;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Source Sans Pro', sans-serif;
        }

        /* 2. Caja del Registro */
        .register-box {
            width: 450px; /* Un poco más ancho que el login por tener más campos */
            margin: 0;
            border: none;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3) !important;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }

        /* 3. Cabecera (Logo) */
        .register-logo {
            margin-bottom: 0;
            padding: 25px 20px 10px;
            text-align: center;
        }
        .register-logo a {
            color: #1e3c72 !important;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 28px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .register-card-body {
            padding: 20px 40px 40px;
            border-radius: 0 !important;
            background: transparent;
        }

        .login-box-msg {
            color: #666;
            font-weight: 600;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        /* 4. Inputs Estilizados */
        .input-group {
            background: #f4f6f9;
            border-radius: 50px;
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
            box-shadow: none;
        }

        .input-group-text {
            background: transparent;
            border: none;
            color: #2a5298;
            font-size: 1.2rem;
        }

        /* 5. Botón */
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
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(42, 82, 152, 0.5);
            background: linear-gradient(90deg, #2a5298 0%, #1e3c72 100%);
        }

        /* Manejo de errores */
        .is-invalid {
            color: #dc3545;
        }
        .input-group.has-error {
            border-color: #dc3545;
            background: #fff8f8;
        }
        
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
    </style>

    <body class="hold-transition register-page">
        <div class="register-box">
            
            <div class="card">
                <div class="register-logo">
                    {{-- Icono decorativo --}}
                    <div style="font-size: 3rem; color: #2a5298; margin-bottom: 10px;">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <a href="{{ url('/home') }}"><b>{{ config('app.name') }}</b></a>
                </div>

                <div class="card-body register-card-body">
                    <p class="login-box-msg">Crear Nueva Cuenta</p>

                    <form method="post" action="{{ route('register') }}">
                        @csrf

                        {{-- Input Nombre --}}
                        <div class="input-group @error('name') has-error @enderror">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-user"></span></div>
                            </div>
                            <input type="text" name="name"
                                class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                placeholder="Nombre Completo" autofocus>
                        </div>
                        @error('name')
                            <span class="d-block text-danger small mb-3 text-center" style="margin-top: -15px;">
                                {{ $message }}
                            </span>
                        @enderror

                        {{-- Input Email --}}
                        <div class="input-group @error('email') has-error @enderror">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
                            </div>
                            {{-- Usamos type="text" para mantener la consistencia con tu login --}}
                            <input type="text" name="email" value="{{ old('email') }}"
                                class="form-control @error('email') is-invalid @enderror" placeholder="Correo Electrónico">
                        </div>
                        @error('email')
                            <span class="d-block text-danger small mb-3 text-center" style="margin-top: -15px;">
                                {{ $message }}
                            </span>
                        @enderror

                        {{-- Input Password --}}
                        <div class="input-group @error('password') has-error @enderror">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-lock"></span></div>
                            </div>
                            <input type="password" name="password"
                                class="form-control @error('password') is-invalid @enderror" 
                                placeholder="Contraseña">
                        </div>
                        @error('password')
                            <span class="d-block text-danger small mb-3 text-center" style="margin-top: -15px;">
                                {{ $message }}
                            </span>
                        @enderror

                        {{-- Input Confirm Password --}}
                        <div class="input-group">
                            <div class="input-group-append">
                                <div class="input-group-text"><span class="fas fa-check-double"></span></div>
                            </div>
                            <input type="password" name="password_confirmation" class="form-control"
                                placeholder="Confirmar Contraseña">
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-block">
                                    REGISTRARSE <i class="fas fa-arrow-right ml-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>

                    <div class="auth-links text-center mt-4">
                        <a href="{{ route('login') }}">
                            ¿Ya tienes cuenta? <b style="color: #2a5298">Inicia Sesión</b>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </body>
</x-laravel-ui-adminlte::adminlte-layout>