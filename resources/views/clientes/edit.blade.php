@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Editar Clientes
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')
        {{-- Llamar a Flash message para mostrar mensajes personalizados desde el controlador --}}
        @include('flash::message')

        <div class="card">

            {!! Form::model($clientes, ['route' => ['clientes.update', $clientes->id_cliente], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('clientes.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Grabar', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('clientes.index') }}" class="btn btn-default"> Cancelar </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
