<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="permissions-table">
            <thead>
                <tr>
                    <th>Permisos</th>
                    <th colspan="3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($permisos as $permiso)
                    <tr>
                        <td>{{ $permiso->name }}</td>
                        <td style="width: 120px">
                            {!! Form::open(['route' => ['permissions.destroy', $permiso->id], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                <a href="{{ route('permissions.edit', [$permiso->id]) }}" class='btn btn-default btn-xs'>
                                    <i class="far fa-edit"></i>
                                </a>
                                {!! Form::button('<i class="far fa-trash-alt"></i>', [
                                    'type' => 'submit',
                                    'class' => 'btn btn-danger btn-xs alert-delete',
                                    'data-mensaje' => $permiso->name,
                                ]) !!}
                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {{-- @include('adminlte-templates::common.paginate', ['records' => $permissions]) --}}
        </div>
    </div>
</div>
