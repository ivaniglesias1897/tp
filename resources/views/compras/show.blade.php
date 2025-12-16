@extends('layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1>Compra #{{ $compra->id_compra }}</h1></div>
      <div class="col-sm-6">
        <a class="btn btn-secondary float-right" href="{{ route('compras.index') }}">
          <i class="fas fa-chevron-left"></i> Volver
        </a>
      </div>
    </div>
  </div>
</section>

<div class="content px-3">
  <div class="card">
    <div class="card-body">
      @include('compras.show_fields')
    </div>
  </div>

  @include('compras.detalle', ['solo_lectura' => true, 'detalles' => $detalles ?? []])
</div>
@endsection