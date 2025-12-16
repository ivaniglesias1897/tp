@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1>
                        Editar Roles
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="content px-3">

        @include('adminlte-templates::common.errors')

        <div class="card">

            {!! Form::model($roles, ['route' => ['roles.update', $roles->id], 'method' => 'patch']) !!}

            <div class="card-body">
                <div class="row">
                    @include('roles.fields')
                </div>
            </div>

            <div class="card-footer">
                {!! Form::submit('Grabar', ['class' => 'btn btn-primary']) !!}
                <a href="{{ route('roles.index') }}" class="btn btn-default"> Cancelar </a>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
@endsection
@push('scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $("#checkall").click(function() {
            $(".child").prop("checked", this.checked);
        });

      //  $('.child').click(function() {
     //       if ($('.child:checked').length == $('.child').length) {
     //       $('#parent').prop('checked', true);
     //       } else {
      //      $('#parent').prop('checked', false);
   //         }
   //     });
    });
</script>
@endpush