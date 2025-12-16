@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="mb-2 row">
                <div class="col-sm-6">
                    <h1>
                        Ventas Detalles
                    </h1>
                </div>
                <div class="col-sm-6">
                    <a class="float-right btn btn-default"
                       href="{{ route('ventas.index') }}">
                       <i class="fas fa-arrow-left"></i> Atras
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="px-3 content">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    @include('ventas.show_fields')
                </div>
            </div>
        </div>
    </div>
@endsection