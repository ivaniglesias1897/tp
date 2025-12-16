@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Pedidos</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('pedidos.create') }}">
                        <i class="fas fa-plus"></i>
                        Nueva Pedido
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('sweetalert::alert')

        <div class="clearfix">
            @includeIf('layouts.buscador', ['url' => url()->current()])
        </div>

        {{-- CORREGIDO: Agregar ID para que AJAX pueda reemplazar el contenido --}}
        <div class="card tabla-container" id="cajas-table-container">
            {{-- Aquí se incluye la tabla inicial --}}
            @include('pedidos.table')
        </div>
    </div>

@endsection