<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table table-hover" id="productos-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th class="text-center">Imagen</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>IVA</th>
                    <th>Marca</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center" colspan="3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($productos as $producto)
                    <tr>
                        <td class="align-middle">{{ $producto->id_producto }}</td>
                        
                        {{-- 1. IMAGEN MEJORADA --}}
                        <td class="text-center align-middle"> 
                            @if ($producto->imagen_producto)
                                <img src="{{ asset('img/productos/' . $producto->imagen_producto) }}"
                                    class="img-thumbnail"
                                    alt="Producto"
                                    style="width: 60px; height: 60px; object-fit: cover;"> 
                            @else
                                {{-- Icono elegante si no hay foto --}}
                                <i class="fas fa-image text-muted" style="font-size: 24px; opacity: 0.5;"></i>
                            @endif
                        </td>

                        <td class="align-middle font-weight-bold">{{ $producto->descripcion }}</td>
                        <td class="align-middle text-nowrap">Gs. {{ number_format($producto->precio, 0, ',', '.') }}</td>
                        
                        {{-- Formato visual del IVA --}}
                        <td class="align-middle">
                            @if($producto->tipo_iva == 0)
                                <span class="badge badge-light">Exenta</span>
                            @else
                                {{ $producto->tipo_iva }}%
                            @endif
                        </td>
                        
                        <td class="align-middle">{{ $producto->marcas }}</td>

                        {{-- 2. ESTADO VISUAL --}}
                        <td class="text-center align-middle">
                            @if($producto->estado)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>

                        <td class="text-center align-middle" style="width: 150px">
                            {!! Form::open(['route' => ['productos.destroy', $producto->id_producto], 'method' => 'delete']) !!}
                            <div class='btn-group'>
                                
                                {{-- 3. EDITAR BLINDADO --}}
                                @can('productos edit')
                                    @if($producto->estado)
                                        <a href="{{ route('productos.edit', [$producto->id_producto]) }}"
                                           class='btn btn-default btn-xs' title="Editar">
                                            <i class="far fa-edit"></i>
                                        </a>
                                    @else
                                        <button type="button" class="btn btn-default btn-xs" disabled title="Debe activar el producto para editar">
                                            <i class="far fa-edit text-muted"></i>
                                        </button>
                                    @endif
                                @endcan

                                {{-- 4. BOTÓN HISTORIAL (Faltaba data-tabla) --}}
                                <button type="button" class="btn btn-info btn-xs btn-historial"
                                    data-id="{{ $producto->id_producto }}" 
                                    data-tabla="productos" 
                                    title="Ver Historial">
                                    <i class="fas fa-history"></i>
                                </button>

                                {{-- 5. TOGGLE ACTIVAR/INACTIVAR --}}
                                @can('productos destroy')
                                    @if ($producto->estado)
                                        {!! Form::button('<i class="fas fa-ban"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-danger btn-xs alert-delete',
                                            'data-mensaje' => 'inactivar el producto ' . $producto->descripcion,
                                            'title' => 'Inactivar'
                                        ]) !!}
                                    @else
                                        {!! Form::button('<i class="fas fa-check"></i>', [
                                            'type' => 'submit',
                                            'class' => 'btn btn-success btn-xs alert-delete',
                                            'data-mensaje' => 'activar el producto ' . $producto->descripcion,
                                            'title' => 'Activar'
                                        ]) !!}
                                    @endif
                                @endcan

                            </div>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-search mb-2" style="font-size: 20px;"></i><br>
                            No se encontraron productos registrados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            {!! $productos->links() !!}
        </div>
    </div>
</div>