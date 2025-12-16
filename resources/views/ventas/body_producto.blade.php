<table class="table">
    <thead>
        <tr>
            <th>Código de Producto</th>
            <th>Producto</th>
            <th>Precio</th>
        </tr>
    </thead>
    <tbody>
        @forelse($productos as $producto)
            <tr onclick="seleccionarProducto('{{ $producto->id_producto }}', '{{ $producto->descripcion }}', '{{ $producto->precio }}')">
                <td>{{ $producto->id_producto }}</td>
                <td>{{ $producto->descripcion }}</td>
                <td>{{ number_format($producto->precio, 0, ',', '.'
                
                ) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3">No se encontraron productos.</td>
            </tr>
        @endforelse
    </tbody>
</table>