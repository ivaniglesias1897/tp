@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Nueva Compra</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-secondary float-right" href="{{ route('compras.index') }}">
                        <i class="fas fa-chevron-left"></i> Volver
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">
        @include('sweetalert::alert')
        <div class="card">
            <div class="card-body">
                @include('adminlte-templates::common.errors')
                
                {!! Form::open(['route' => 'compras.store', 'id' => 'form-compra']) !!}

                    <div class="row">
                        {{-- Aquí adentro ya está incluido 'compras.detalle', así que no hace falta ponerlo afuera --}}
                        @include('compras.fields')
                    </div>

                    {{-- 
                        <--- LÍNEA ELIMINADA: @include('compras.detalle') 
                        Se borró para evitar que la tabla de productos salga duplicada.
                    --}}

                    <div class="form-group mt-4">
                        {!! Form::submit('Guardar Compra', ['class' => 'btn btn-primary']) !!}
                        <a href="{{ route('compras.index') }}" class="btn btn-default">Cancelar</a>
                    </div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
@endsection