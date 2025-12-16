@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Roles</h1>
                </div>
                <div class="col-sm-6">
                    <a class="btn btn-primary float-right"
                       href="{{ route('roles.create') }}">
                       <i class="fas fa-plus"></i>
                        Agregar Rol
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

        <!-- agregar la clase tabla-container para mostrar los valores filtrados de table-->
        <div class="card tabla-container">
            @include('roles.table')
        </div>
    </div>

@endsection
