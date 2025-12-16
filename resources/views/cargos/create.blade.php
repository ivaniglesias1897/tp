@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Crear Nuevo Cargo
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::open(['route' => 'cargos.store']) !!}
                <div class="card-body">

                    <div class="row">
                        @include('cargos.fields')
                    </div>

                </div>

                <div class="card-footer">
                    {!! Form::submit('Grabar', ['class' => 'btn btn-primary']) !!}
                    <a href="{{ route('cargos.index') }}" class="btn btn-default"> Cancelar </a>
                </div>
            {!! Form::close() !!}

        </div>
    </div>
@endsection
