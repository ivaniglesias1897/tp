<div class="timeline">
    @forelse($historial as $item)
        <!-- Etiqueta de Fecha -->
        <div class="time-label">
            <span class="bg-secondary px-2">{{ $item['fecha'] }}</span>
        </div>

        <div>
            <!-- Icono según la acción -->
            @if($item['accion'] == 'INSERT')
                <i class="fas fa-plus bg-success"></i>
            @elseif($item['accion'] == 'UPDATE')
                <i class="fas fa-pencil-alt bg-primary"></i>
            @elseif($item['accion'] == 'DELETE')
                <i class="fas fa-trash bg-danger"></i>
            @endif

            <div class="timeline-item">
                <span class="time"><i class="far fa-clock"></i> {{ $item['fecha'] }}</span>
                
                <h3 class="timeline-header">
                    <a href="#">{{ $item['usuario'] }}</a> 
                    
                    @if($item['accion'] == 'INSERT')
                        realizó una <strong class="text-success">Creación</strong>
                    @elseif($item['accion'] == 'UPDATE')
                        realizó una <strong class="text-primary">Modificación</strong>
                    @else
                        realizó una <strong class="text-danger">Eliminación</strong>
                    @endif
                </h3>

                <div class="timeline-body">
                    @if(count($item['cambios']) > 0)
                        <ul class="list-unstyled mb-0 pl-2">
                            @foreach($item['cambios'] as $cambio)
                                <li class="text-sm border-bottom pb-1 mb-1">
                                    <i class="fas fa-angle-right text-muted mr-2"></i>
                                    {{-- Usamos {!! !!} para interpretar el HTML (negritas y colores) que manda el controlador --}}
                                    {!! $cambio !!}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-muted font-italic">No se registraron detalles específicos de cambios.</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-light text-center m-4">
            <div class="text-muted mb-2">
                <i class="fas fa-history fa-3x"></i>
            </div>
            <p class="text-muted">No existe historial de cambios para este registro.</p>
        </div>
    @endforelse
    
    <!-- Icono final -->
    <div>
        <i class="fas fa-clock bg-gray"></i>
    </div>
</div>