@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-12">
                    <h1>
                        Edit Pedidos
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="px-3 content">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::model($pedido, ['route' => ['pedidos.update', $pedido->id_pedido], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('pedidos.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Grabar', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('pedidos.index') }}" class="btn btn-default"> Cancelar </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection