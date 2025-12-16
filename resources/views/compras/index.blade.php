@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Compras</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right" href="{{ route('compras.create') }}">
                        <i class="fas fa-plus"></i> Nueva Compra
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        {{-- @include('flash::message') --}}
        @include('sweetalert::alert')

        <div class="clearfix">
            @includeIf('layouts.buscador', ['url' => url()->current()])
        </div>

        {{-- PASO 2: Este es el ÚNICO contenedor donde se inyecta el resultado AJAX. --}}
        <div class="card tabla-container">
            {{-- Aquí se incluye la tabla inicial --}}
            @include('compras.table')
        </div>
    </div>
@endsection
