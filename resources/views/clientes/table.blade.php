<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="clientes-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nombre Completo</th>
                    <th>Cédula (CI)</th>
                    <th>Teléfono</th>
                    <th>Ubicación</th>
                    <th>Edad</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clientes as $cliente)
                    <tr>
                        <td>{{ $cliente->id_cliente }}</td>
                        <td>{{ $cliente->clie_nombre }} {{ $cliente->clie_apellido }}</td>
                        <td>{{ number_format((int)$cliente->clie_ci, 0, ',', '.') }}</td>
                        <td>{{ $cliente->clie_telefono ?? '-' }}</td>
                        
                        {{-- Combinamos Ciudad y Departamento para ahorrar espacio --}}
                        <td>
                            <small class="d-block font-weight-bold">{{ $cliente->ciudad }}</small>
                            <small class="text-muted">{{ $cliente->departamento }}</small>
                        </td>

                        {{-- Cálculo de edad visual (usando Carbon) --}}
                        <td>
                            {{ \Carbon\Carbon::parse($cliente->clie_fecha_nac)->age }} años
                        </td>
                        
                        {{-- 1. ESTADO VISUAL --}}
                        <td class="text-center">
                            @if($cliente->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center" style="width: 150px">
                            {!! Form::open(['route' => ['clientes.destroy', $cliente->id_cliente], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 2. EDITAR BLINDADO --}}
                                @can('clientes edit')
                                    @if($cliente->estado)
                                        <a href="{{ route('clientes.edit', [$cliente->id_cliente]) }}" class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar el cliente para editar">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 3. HISTORIAL --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $cliente->id_cliente }}" 
                                    data-tabla="clientes"
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 4. TOGGLE ACTIVAR/INACTIVAR --}}
                                @can('clientes destroy')
                                    @if ($cliente->estado)
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar al cliente ' . $cliente->clie_nombre,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar al cliente ' . $cliente->clie_nombre,
                                            'title' => 'Activar'
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
            {!! $clientes->links() !!}
        </div>
    </div>
</div>