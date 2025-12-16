@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-12">
                    <h1>
                        Nuevo Producto
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="px-3 content">

        @include('adminlte-templates::common.errors')
        @include('sweetalert::alert')

        <div class="card">

            {{-- Agregamos 'files' => true para permitir subida de imágenes --}}
            {!! Form::open(['route' => 'productos.store', 'files' => true]) !!}

            <div class="card-body">

                <div class="row">
                    @include('productos.fields')
                </div>

            </div>

            <div class="card-footer">
                {!! Form::submit('Grabar', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('productos.index') }}" class="btn btn-default"> Cancelar </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
