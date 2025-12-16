@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-12">
                    <h1>
                        Editar Ventas
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="px-3 content">

        @include('adminlte-templates::common.errors')
        @include('sweetalert::alert')

        <div class="card">

            {!! Form::model($ventas, ['route' => ['ventas.update', $ventas->id_venta], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('ventas.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Grabar', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('ventas.index') }}" class="btn btn-default"> Cancelar </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection