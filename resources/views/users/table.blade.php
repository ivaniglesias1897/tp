<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre y Apellido</th>
                    <th>Username</th>
                    <th>Nro Doc.</th>
                    <th>Teléfono</th>
                    <th>Fecha Ingreso</th>
                    <th>Estado</th>
                    <th>Rol</th>
                    <th>Sucursal</th>
                    <th colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->ci }}</td>
                        <td>{{ $user->telefono }}</td>
                        <td>{{ !empty($user->fecha_ingreso) ? Carbon\Carbon::parse($user->fecha_ingreso)->format('d/m/Y') : '' }}
                        </td>
                        <td>{{ $user->estado == true ? 'Activo' : 'Inactivo' }}</td>
                        <td>{{ $user->rol }}</td>
                        <td>{{ $user->sucursal }}</td>
                        <td style="width: 120px">
                            {!! Form::open(['route' => ['users.destroy', $user->id], 'method' => 'delete']) !!}
                            <div class='btn-group'>

                                @can('users edit')
                                    {{-- CAMBIO: Solo mostramos el botón si el usuario está ACTIVO --}}
                                    @if ($user->estado)
                                        <a href="{{ route('users.edit', [$user->id]) }}" class='btn btn-default btn-xs'
                                            title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        {{-- Opcional: Mostramos un botón deshabilitado gris para que no quede el hueco --}}
                                        <button class="btn btn-default btn-xs" disabled
                                            title="No se puede editar un inactivo">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 1. BOTÓN HISTORIAL (NUEVO) --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $user->id }}" data-tabla="users" title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                @can('users destroy')
                                    @if ($user->estado == true)
                                        {{-- 2. Botón INACTIVAR (Antes era Basurero, ahora es Bloqueo) --}}
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar al usuario ' . $user->name,
                                            'title' => 'Inactivar Acceso',
                                        ]) !!}
                                    @else
                                        {{-- 3. Botón ACTIVAR (Se mantiene igual) --}}
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar al usuario ' . $user->name,
                                            'title' => 'Activar Acceso',
                                        ]) !!}
                                    @endif
                                @endcan
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
            {{ $users->links() }}
        </div>
    </div>
</div>
